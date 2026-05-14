<?php

namespace Abbott\ShoppingCart\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestBuilder;
use Abbott\ShoppingCart\Exception\MaxCartQtyException;
use Abbott\ShoppingCart\Exception\AlreadyInCartException;
use Abbott\ShoppingCart\Exception\QtyNotAvailableException;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

class AddSimpleProductToCart extends \Magento\QuoteGraphQl\Model\Cart\AddSimpleProductToCart
{
    public $collection;
    /**
     * @var \Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory
     */
    public $collectionFactory;
    public $dataHelper;
    public $checkoutHelper;
    public $optionRepository;
    const SUB_TYPE = 'aw_sarp2_subscription_type';
    const BACKORDER = 4;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Aheadworks\Sarp2\Block\Customer\Subscriptions
     */
    protected $subscriptions;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $helper;

    protected $metabolicData;

    /**
     *
     * @var \Abbott\StockManagement\Helper\Data
     */
    protected $stockHelper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param Collection $collection
     * @param CollectionFactory $collectionFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Aheadworks\Sarp2\Block\Customer\Subscriptions $subscriptions
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Json\Helper\Data $helper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        BuyRequestBuilder $buyRequestBuilder,
        Collection $collection,
        CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Aheadworks\Sarp2\Block\Customer\Subscriptions $subscriptions,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Json\Helper\Data $helper,
        \Abbott\ProgressiveDiscount\Helper\Data $dataHelper,
        \Abbott\StockManagement\Helper\Data $stockHelper,
        \Abbott\Checkout\Helper\Data $checkoutHelper,
        SubscriptionOptionRepositoryInterface $optionRepository,
        MetabolicData $metabolicData
    ) {
        $this->productRepository = $productRepository;
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->subscriptions = $subscriptions;
        $this->cookieManager = $cookieManager;
        $this->stockHelper = $stockHelper;
        $this->collection = $collection;
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->dataHelper = $dataHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->optionRepository = $optionRepository;
        $this->metabolicData = $metabolicData;
        parent::__construct($productRepository, $buyRequestBuilder);
    }

    /**
     * Add simple product to cart
     *
     * @param Quote $cart
     * @param array $cartItemData
     * @return void
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Quote $cart, array $cartItemData): void
    {
        $sku = $this->extractSku($cartItemData);
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }
        try {
            $productId = $product->getId();
            $stockManager = $this->stockRegistry->getStockItem($productId);
            $items = $cart->getItems();
            $itemQty = 0;
            $storeId = $this->storeManager->getStore()->getStoreId();
            $storeCode = $this->storeManager->getStore()->getCode();
            $maxQtyForProduct = $stockManager->getMaxSaleQty();
            $minQtyForProduct = $stockManager->getMinSaleQty();
            $requestedQty = $cartItemData['data']['quantity'];
            $minMaxMsg = "Min " . $minQtyForProduct . " and Max " . $maxQtyForProduct . " Quantity required";

            // check for Metabolic
            if ($product->getData()['order_on_call']) {
                $customerEmail = $this->customerSession->getCustomer()->getEmail();
                if ($customerEmail) {
                    $data['sku'] = $sku;
                    $data['customer_email'] = $customerEmail;
                    $metabolicDataResult = $this->metabolicData->ifExistingRecord($data);
                    $metabolicQty = $metabolicDataResult['qty'];
                    $quoteItemQty = $cartItemData['data']['quantity'];
                    if ($metabolicQty < $quoteItemQty) {
                        throw new MaxCartQtyException(
                            __('The most you may purchase is ' . $metabolicQty)
                        );
                    }
                }
            }

            //check wether current product has progressive option selected
            $isProgressive = $this->getItemOptionIsProgressive($cartItemData);
            if (array_key_exists(self::SUB_TYPE, $cartItemData['data']) && $this->checkoutHelper->isEnabledQuantityValidation()) {
                //add product min and max quantity validation for is progressive
                $requested_qty = $cartItemData['data']['quantity'] + $this->checkExistingQty($items,$productId);
                $isValidQty = $this->checkoutHelper->validateProductQuantity($productId, $requested_qty, '', $storeId, $showMessage = true);
                if (!empty($isValidQty)) {
                    throw new MaxCartQtyException(__($isValidQty));
                }
            }



            if ($this->stockHelper->getConfigValue() && $this->stockHelper->checkStock($product) == self::BACKORDER && $product->getData()['quantity_and_stock_status']['is_in_stock']) {
                $requestedQty = $cartItemData['data']['quantity'] + $this->checkExistingQty($items,$productId);
                $diff = $stockManager->getQty() - $product->getThreshold();
                if ($diff < $maxQtyForProduct && $requestedQty > $diff) {
                    throw new MaxCartQtyException(__(
                        'Could not add the product with SKU '. $sku . ' to the shopping cart. The requested quantity is not available.',
                    ));
                }
            }

            $itemQty = $this->getItemQty($sku, $items, $cartItemData);
            if (!array_key_exists(self::SUB_TYPE, $cartItemData['data']) && $storeId == 1 || $storeCode == \Abbott\MyAccount\Helper\Data::NEW_SIM_STORE_CODE) {
                if (($itemQty && ($cartItemData['data']['quantity'] + $itemQty) > $maxQtyForProduct) || $cartItemData['data']['quantity'] > $maxQtyForProduct) {
                    throw new MaxCartQtyException(
                        __('The most you may purchase is ' . $maxQtyForProduct)
                    );
                } elseif (($cartItemData['data']['quantity'] + $itemQty) < $minQtyForProduct) {
                    throw new MaxCartQtyException(
                        __($minMaxMsg)
                    );
                }
            } elseif ($storeId == 2) {
                $cart->removeAllItems();
            } elseif ($storeId == 3) {
                $this->getActiveSubscription($cart);
                if ($cart->getItemsCount() > 0) {
                    throw new MaxCartQtyException(
                        __('You can only add one product to the cart')
                    );
                }
            }


            //check for checkout restriction added at backend
            if (!empty($this->dataHelper->getIsProgressiveCheckoutRestricted($storeId))) {
                $customerId = $cart->getCustomer()->getId();
                if (!empty($isProgressive)) {
                    $this->validateCartItems($items, $customerId);
                }
            }

            $result = $cart->addProduct($product, $this->buyRequestBuilder->build($cartItemData));
        } catch (AlreadyInCartException $alreadyInCartException) {
            throw new GraphQlInputException(
                __($alreadyInCartException->getMessage())
            );
        } catch (QtyNotAvailableException $qtyNotAVailable) {
            throw new QtyNotAvailableException(
                __($qtyNotAVailable->getMessage())
            );
        } catch (MaxCartQtyException $maxCartQtyException) {
            throw new GraphQlInputException(
                __($maxCartQtyException->getMessage())
            );
        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __(
                    'Could not add the product with SKU "%sku" to the shopping cart: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
                )
            );
        }

        if (is_string($result)) {
            throw new GraphQlInputException(__($result));
        }
    }

    /**
     * Extract SKU from cart item data
     *
     * @param array $cartItemData
     * @return string
     * @throws GraphQlInputException
     */
    private function extractSku(array $cartItemData): string
    {
        // Need to keep this for configurable product and backward compatibility.
        if (!empty($cartItemData['parent_sku'])) {
            return (string)$cartItemData['parent_sku'];
        }
        if (empty($cartItemData['data']['sku'])) {
            throw new GraphQlInputException(__('Missed "sku" in cart item data'));
        }
        return (string)$cartItemData['data']['sku'];
    }

    /**
     * Get qty of current item
     *
     * @param array $cartItemData
     * @return int
     */
    public function checkExistingQty($items,$productId)
    {
        foreach ($items as $item) {
            if($productId == $item->getProductId()){
            return $item->getQty();
            }
        }
    }

    public function getItemQty($sku, $items, $cartItemData)
    {
        foreach ($items as $item) {
            $options = $item->getOptionsByCode();
            if ($sku == $item->getProduct()->getSku()) {
                return $this->getItemExists($options, $item, $cartItemData);
            }
        }
        return false;
    }

    public function getItemExists($options, $item, $cartItemData)
    {
        if (array_key_exists(self::SUB_TYPE, $cartItemData['data'])) {
            throw new AlreadyInCartException(__('The product is already added in your cart.'));
        } elseif (array_key_exists(self::SUB_TYPE, $options)) {
            throw new AlreadyInCartException(__('The product is already added in your cart.'));
        } else {
            return $item->getQty();
        }
    }

    public function getActiveSubscription($cart)
    {
        $customerId = $cart->getCustomer()->getId();
        $this->collection = $this->collectionFactory->create();
        $this->collection
            ->addFieldToFilter(
                ProfileInterface::CUSTOMER_ID,
                ['eq' => $customerId]
            )
            ->addOrder(ProfileInterface::CREATED_AT, Collection::SORT_ORDER_DESC);
        $profiles = $this->collection;
        if ($profiles && count($profiles)) {
            $this->getProfileCheck($profiles);
        }
    }

    public function getProfileCheck($profiles)
    {
        foreach ($profiles as $profile) {
            if ($this->subscriptions->getStatusLabel($profile->getStatus()) == 'Active') {
                throw new MaxCartQtyException(
                    __('You already have an active subscription')
                );
            }
        }
    }

    /**
     * Extract Plan type from sku
     *
     * @param string $sku
     * @param array $cartItemData
     * @return booloean
     */
    public function getItemOptionIsProgressive($cartItemData)
    {
        if (array_key_exists(self::SUB_TYPE, $cartItemData['data'])) {
            $optionId = $cartItemData['data'][self::SUB_TYPE];
            if (!empty($optionId)) {
                $option = $this->optionRepository->get($optionId);
                $planId = $option->getPlanId();
                if (!empty($planId)) {
                    return $this->dataHelper->getIsProgressive($planId);
                }
            }
        }
        return false;
    }

    /**
     *
     */
    public function validateCartItems($items, $customerId = '')
    {
        if (!empty($customerId)) {
            //check for customer has progressive discount active
            if ($this->dataHelper->isSubscriptionActive($customerId)) {
                $message = (!empty($this->dataHelper->getActiveSubscriptionCheckoutMessage()) ? $this->dataHelper->getActiveSubscriptionCheckoutMessage() : 'You already have an active subscription that uses this special offer for an average savings of 20%');
                throw new GraphQlInputException(__($message));
            } else {
                $flag = $this->dataHelper->checkCartItems($items, 'active');
                if ($flag) {
                    $message = (!empty($this->dataHelper->getProductSubscriptionCheckoutMessage()) ? $this->dataHelper->getProductSubscriptionCheckoutMessage() : 'You already have an item in your cart that uses this special subscrfiption offer');
                    throw new AlreadyInCartException(__($message));
                }
            }
        } else {
            $flag = $this->dataHelper->checkCartItems($items, 'active');
            if ($flag) {
                $message = (!empty($this->dataHelper->getProductSubscriptionCheckoutMessage()) ? $this->dataHelper->getProductSubscriptionCheckoutMessage() : 'You already have an item in your cart that uses this special subscrfiption offer');
                throw new AlreadyInCartException(__($message));
            }
        }
    }
}
