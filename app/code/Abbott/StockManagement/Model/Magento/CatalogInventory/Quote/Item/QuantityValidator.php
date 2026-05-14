<?php
namespace Abbott\StockManagement\Model\Magento\CatalogInventory\Quote\Item;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Helper\Data;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;

class QuantityValidator extends \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator
{
    public $productRepository;
    const BACKORDER = 4;
    /**
     *
     * @var \Abbott\StockManagement\Helper\Data
     */
    protected $dataHelper;
    /**
     *
     * @param \Abbott\StockManagement\Helper\Data $dataHelper
     */
    public function __construct(
        \Abbott\StockManagement\Helper\Data $dataHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Option $optionInitializer,
        StockItem $stockItemInitializer,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState
    ) {
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->optionInitializer = $optionInitializer;
        $this->stockItemInitializer = $stockItemInitializer;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
        parent::__construct($optionInitializer, $stockItemInitializer, $stockRegistry, $stockState);
    }

    public function validate(Observer $observer)
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
        $qty = $quoteItem->getQty();

        /* @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        if (!$stockItem instanceof StockItemInterface) {
            throw new LocalizedException(__('The Product stock item is invalid. Verify the stock item and try again.'));
        }

        if (($options = $quoteItem->getQtyOptions()) && $qty > 0) {
            foreach ($options as $option) {
                $this->optionInitializer->initialize($option, $quoteItem, $qty);
            }
        } else {
            $this->stockItemInitializer->initialize($stockItem, $quoteItem, $qty);
        }

        if ($quoteItem->getQuote()->getIsSuperMode()) {
            return;
        }

        /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus */
        $stockStatus = $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId());

        /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $parentStockStatus */
        $parentStockStatus = false;

        /**
         * Check if product in stock. For composite products check base (parent) item stock status
         */
        if ($quoteItem->getParentItem()) {
            $product = $quoteItem->getParentItem()->getProduct();
            $parentStockStatus = $this->stockRegistry->getStockStatus(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
        }


        if ($stockStatus) {
            $product = $quoteItem->getProduct();
            $productData = $this->productRepository->getById(
                $product->getId(),
                false,
                $product->getStore()->getStoreId()
            );
            if ($stockStatus->getStockStatus() === Stock::STOCK_OUT_OF_STOCK
                || $parentStockStatus && $parentStockStatus->getStockStatus() == Stock::STOCK_OUT_OF_STOCK
            ) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    Data::ERROR_QTY,
                    __('This product is out of stock.')
                );
                $quoteItem->getQuote()->addErrorInfo(
                    'qty',
                    'cataloginventory',
                    Data::ERROR_QTY,
                    __('Please see error below and update your cart.')
                );
                return;
            } else {
                // Delete error from item and its quote, if it was set due to item out of stock
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
            }
        }
        $this->checkItemForOptions($options, $product, $stockStatus, $quoteItem);
    }

    private function checkItemForOptions($options, $product, $stockStatus, $quoteItem)
    {
        /**
         * Check item for options
         */
        if ($options) {
            $qty = $product->getTypeInstance()->prepareQuoteItemQty($quoteItem->getQty(), $product);
            $quoteItem->setData('qty', $qty);
            if ($stockStatus) {
                $this->checkOptionsQtyIncrements($quoteItem, $options);
            }

            // variable to keep track if we have previously encountered an error in one of the options
            $removeError = true;
            foreach ($options as $option) {
                $result = $option->getStockStateResult();
                if ($result->getHasError()) {
                    $option->setHasError(true);
                    //Setting this to false, so no error statuses are cleared
                    $removeError = false;
                    $this->addErrorInfoToQuote($result, $quoteItem, $removeError);
                }
            }
            if ($removeError) {
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
            }
        } else {
            if ($quoteItem->getParentItem() === null) {
                $result = $quoteItem->getStockStateResult();
                if ($result->getHasError()) {
                    $this->addErrorInfoToQuote($result, $quoteItem);
                } else {
                    $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
                }
            }
        }
    }

    /**
     * Add error information to Quote Item
     *
     * @param \Magento\Framework\DataObject $result
     * @param Item $quoteItem
     * @return void
     */
    private function addErrorInfoToQuote($result, $quoteItem)
    {
        $quoteItem->addErrorInfo(
            'cataloginventory',
            Data::ERROR_QTY,
            $result->getMessage()
        );

        $quoteItem->getQuote()->addErrorInfo(
            $result->getQuoteMessageIndex(),
            'cataloginventory',
            Data::ERROR_QTY,
            $result->getQuoteMessage()
        );
    }
}
