<?php

namespace Abbott\StockManagement\Plugin;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Helper\Data;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote\Item;
use Magento\Customer\Model\Session as CustomerSession;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

class QuantityValidatorPlugin
{
    public $productRepository;
    public $stockRegistry;
    const BACKORDER = 4;
    const BRAND = 'Metabolics';
    const LEVEL = 'Level1';
    public const OUT_OF_STOCK_MSG = 'Some of the products are out of stock.';
    public const PRODUCT_OUT_OF_STOCK_MSG = 'This product is out of stock.';
    public const GENERIC_MESSAGE = 'Please see error below and update your cart.';
    /**
     *
     * @var \Abbott\StockManagement\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $customerSession;

    protected $validateMetabolicOrderingProduct;

    protected $metabolicData;

    public function __construct(
        \Abbott\StockManagement\Helper\Data $dataHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StockRegistryInterface $stockRegistry,
        CustomerSession $customerSession,
        \Abbott\StockManagement\Helper\Data $validateMetabolicOrderingProduct,
        MetabolicData $metabolicData
    ) {
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
        $this->customerSession = $customerSession;
        $this->validateMetabolicOrderingProduct = $validateMetabolicOrderingProduct;
        $this->metabolicData = $metabolicData;
    }
    public function afterValidate(QuantityValidator $subject, $result, $observer)
    {
        /* @var $quoteItem Item */
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem ||
            !$quoteItem->getProductId() ||
            !$quoteItem->getQuote()
        ) {
            return;
        }
        $product = $quoteItem->getProduct();
        $productSku = $product->getSku();
        $customerEmail = $this->customerSession->getCustomer()->getEmail();
        $stockStatus = $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId());
        if ($stockStatus) {
            $productData = $this->productRepository->getById(
                $product->getId(),
                false,
                $product->getStore()->getStoreId()
            );
            $qty = $productData->getData()['quantity_and_stock_status']['qty'];
            $threshold = $productData->getThreshold();
            if ($this->dataHelper->getConfigValue() &&
                $this->dataHelper->checkStock($productData) == self::BACKORDER &&
                $productData->getData()['quantity_and_stock_status']['is_in_stock'] &&
                $threshold >= $qty) {
                $quoteItem->removeErrorInfosByParams('origin');
                $this->addQuoteErrorInfo($quoteItem);
            } elseif (($productData->getData()['order_on_call']) &&
                ($this->metabolicData->getLevelAttributeLabel(
                    $product->getSku()
                ) != self::LEVEL) &&
                (!$this->validateMetabolicOrderingProduct->validateMetabolicOrderingProduct($customerEmail, $productSku)
                )) {
                    $this->addQuoteErrorInfo($quoteItem);
            } elseif (($productData->getData()['order_on_call']) &&
                ($this->metabolicData->getLevelAttributeLabel(
                    $product->getSku()
                ) == self::LEVEL) &&
                ($this->validateMetabolicOrderingProduct->validateMetabolicOrderingProduct(
                    $customerEmail,
                    $productSku
                )
                )) {
                $customerEmail = $this->customerSession->getCustomer()->getEmail();
                $productSku = $product->getSku();
                if (!$this->validateMetabolicOrderingProduct->validateMetabolicOrderingProduct(
                    $customerEmail,
                    $productSku
                )) {
                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        Data::ERROR_QTY,
                        __(self::PRODUCT_OUT_OF_STOCK_MSG)
                    );
                    $quoteItem->getQuote()->addErrorInfo(
                        'qty',
                        'cataloginventory',
                        Data::ERROR_QTY,
                        __(self::GENERIC_MESSAGE)
                    );
                }
            } else {
                return null;
            }
        }
    }

    private function addQuoteErrorInfo($quoteItem)
    {
        $quoteItem->addErrorInfo(
            'cataloginventory',
            Data::ERROR_QTY,
            __(self::PRODUCT_OUT_OF_STOCK_MSG)
        );
        $quoteItem->getQuote()->addErrorInfo(
            'qty',
            'cataloginventory',
            Data::ERROR_QTY,
            __(self::GENERIC_MESSAGE)
        );
    }
}
