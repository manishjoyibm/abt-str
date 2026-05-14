<?php

namespace Abbott\StockManagement\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Customer\Model\Session as CustomerSession;
use Abbott\Checkout\Helper\Data  as CheckoutDataHelper;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

class UpdateCartItemPlugin
{
    public $productRepository;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    const BACKORDER = 4;
    const BRAND = 'Metabolics';
    const AVAILABLE_FOR_CALL = 1;
    const LEVEL = 'Level1';

      /**
       * @var GetCartForUser
       */
    private $getCartForUser;

    /**
     *
     * @var \Abbott\StockManagement\Helper\Data
     */
    protected $dataHelper;

    protected $customerSession;

    protected $checkoutDataHelper;

    protected $metabolicData;


    /**
     *
     * @param GetCartForUser $getCartForUser
     * @param \Abbott\StockManagement\Helper\Data $dataHelper
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        \Abbott\StockManagement\Helper\Data $dataHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        CheckoutDataHelper $checkoutDataHelper,
        MetabolicData $metabolicData
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->checkoutDataHelper = $checkoutDataHelper;
        $this->metabolicData = $metabolicData;
    }

    public function beforeResolve(
        \Magento\QuoteGraphQl\Model\Resolver\UpdateCartItems $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $maskedCartId = $args['input']['cart_id'];
        $cartItems = $args['input']['cart_items'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        foreach ($cartItems as $item) {
            $itemId = (int)$item['cart_item_id'];
            $cartItem = $cart->getItemById($itemId);
            $productId = $cartItem->getProductId();
            $isSubscription = 0;
            if ($cartItem->getData('aw_sarp_regular_row_total') != 0) {
                $isSubscription = 1;
            }
            $qty = $item['quantity'];
            $product = $this->productRepository->getById(
                $productId,
                false,
                $cartItem->getStoreId()
            );


            // check allowed qty for metabolic products
            $customerEmail = $this->customerSession->getCustomer()->getEmail();
            $sku = $product->getSku();
            if (($customerEmail != null) &&
                ($this->metabolicData->getLevelAttributeLabel($sku) == self::LEVEL) &&
                ($product->getOrderOnCall() == self::AVAILABLE_FOR_CALL)) {

                $qty = $item['quantity'];
                $isValidQty = $this->checkoutDataHelper->validateProductQuantityForMetabolic(
                    $sku,
                    $qty,
                    $customerEmail
                );
                if (!empty($isValidQty)) {
                    throw new GraphQlInputException(__($isValidQty));
                }
            }

            $maxQty = $this->dataHelper->checkStockData($product, 'max_sale_qty');

            if (!$isSubscription && $this->dataHelper->getConfigValue() &&
                $this->dataHelper->checkStock($product) == self::BACKORDER &&
                $product->getData()['quantity_and_stock_status']['is_in_stock']) {
                $thresold = $this->dataHelper->getThreshold($product, $isSubscription, 1);
                if ($maxQty > 0 && $thresold == $maxQty && $qty > $maxQty) {
                        throw new GraphQlInputException(
                            __(
                                'The most you may purchase is '.round($maxQty)
                            )
                        );
                } elseif ($thresold < $qty) {
                    throw new GraphQlInputException(
                        __(
                            'Could not update the product with SKU '.$cartItem->getSku().' to the
                            shopping cart. The requested quantity is not available.'
                        )
                    );
                }
            }
        }
    }
}
