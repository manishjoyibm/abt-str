<?php

namespace Abbott\OrderManagement\Plugin;

use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Configuration;

class CancelOrderItemObserver
{

     /**
      * @var \Magento\CatalogInventory\Model\Configuration
      */
    protected $configuration;

    /**
     * @var StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $priceIndexer;

    /**
     * @param Configuration $configuration
     * @param StockManagementInterface $stockManagement
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     */
    public function __construct(
        Configuration $configuration,
        StockManagementInterface $stockManagement,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
    ) {
        $this->configuration = $configuration;
        $this->stockManagement = $stockManagement;
        $this->priceIndexer = $priceIndexer;
    }

    public function aroundExecute(
        \Magento\CatalogInventory\Observer\CancelOrderItemObserver $subject,
        callable $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getEvent()->getItem();
        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        if ($item->getId() && $item->getProductId() && empty($children) && $qty) {
            $this->stockManagement->backItemQty($item->getProductId(), $qty, $item->getStore()->getWebsiteId());
        }
        $this->priceIndexer->reindexRow($item->getProductId());
    }
}
