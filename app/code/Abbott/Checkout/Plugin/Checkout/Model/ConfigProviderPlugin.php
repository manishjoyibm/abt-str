<?php

namespace Abbott\Checkout\Plugin\Checkout\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;

class ConfigProviderPlugin extends \Magento\Framework\Model\AbstractModel
{
    public $productFactory;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $currencyHelper;
    public $storeManager;
    public $checkoutSession;
    public $quoteItemRepository;
    public $planCollectionFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $quoteModel;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteModel
     * @param \Magento\Catalog\Model\Product $productModel
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Item $quoteModel,
        \Magento\Catalog\Model\Product $productModel,
        CheckoutSession $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        CurrencyHelper $currencyHelper,
        QuoteItemRepository $quoteItemRepository,
        \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription\CollectionFactory $planCollectionFactory
    ) {
        $this->quoteModel = $quoteModel;
        $this->productModel = $productModel;
        $this->productFactory = $productFactory;
        $this->currencyHelper = $currencyHelper;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->planCollectionFactory = $planCollectionFactory;
    }

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $items = $result['totalsData']['items'];
        $storeId = $this->storeManager->getStore()->getId();
        foreach ($items as $index => $item) {
            $quoteId = $item['item_id'];
            $quote = $this->quoteModel->load($quoteId);
            $productId = $quote->getProductId();
            $product = $this->productModel->load($productId);
            $productFlavours = $product->getResource()->getAttribute('flavors')->getFrontend()->getValue($product);
            $result['quoteItemData'][$index]['flavor'] = $productFlavours;
        }

        if ($storeId == 2) {
            $result['associatedData'] = $this->getAssociatedData();
        }
        return $result;
    }

    private function getAssociatedData()
    {
        $associatedData = [];
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $quoteItems = $this->quoteItemRepository->getList($quoteId);
            foreach ($quoteItems as $index => $quoteItem) {
                if ($index == 0) {
                    $product = $this->productFactory->create();
                    $product->getResource()->load($product, $quoteItem->getProduct()->getId());
                    $allowTrial = $product->getData('allow_trial');
                    $associatedSku = $product->getData('actual_trial_sku_mapping');
                    $actualDeliverySplit = $product->getData('glucerna_delivery_split');
                    $actualProductPrice = $product->getData('price');
                    $associatedProduct = $this->productFactory->create();
                    $associatedProduct->load($associatedProduct->getIdBySku($associatedSku));
                    $associatedDeliverySplit = $associatedProduct->getData('glucerna_delivery_split');
                    $groupSkus = $associatedProduct->getData('group_sku');
                    $associatedProductPrice = $associatedProduct->getData('price');
                    $planName = $product->getAttributeText('glucerna_product_plan');
                    $planCollection = $this->planCollectionFactory->create()
                                       ->addFieldToFilter('plan_name', $planName)->getFirstItem();
                    $trialPeriod = $planCollection['trial_period'] ? $planCollection['trial_period'] : 0;
                    $associatedData['allow_trial'] = $allowTrial;
                    $associatedData['associatedSku'] = $associatedSku;
                    $associatedData['productNames'] = $this->getProductNames($groupSkus);
                    $associatedData['actualShakes'] = array_sum(explode(',', $actualDeliverySplit));
                    $associatedData['actualDeliverySplit'] = explode(',', $actualDeliverySplit);
                    $associatedData['associatedDeliverySplit'] = explode(',', $associatedDeliverySplit);
                    $associatedData['actualProductPrice'] = $this->currencyHelper->currency(
                        number_format($actualProductPrice, 2),
                        true,
                        false
                    );
                    $associatedData['associatedProductPrice'] = $this->currencyHelper->currency(
                        number_format($associatedProductPrice, 2),
                        true,
                        false
                    );
                    $associatedData['associatedRenewDate'] = date("m/d/Y", strtotime("+".$trialPeriod." days"));
                    $associatedData['actualRenewDate'] = date("m/d/Y", strtotime("+28 days"));
                }
            }
        }
        return $associatedData;
    }

    private function getProductNames($groupSkus)
    {
        $skus = explode(',', $groupSkus);
        $names = [];
        foreach ($skus as $sku) {
            $product = $this->productFactory->create();
            $product->load($product->getIdBySku($sku));
            $names[] = $product->getData('product_flavor');
        }
        return $names;
    }
}
