<?php

namespace Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search\Grid\DataProvider;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\Store;
use Magento\Framework\App\ResourceConnection;

/**
 * Prepares product collection for the grid
 */
class ProductCollection
{
    public $resource;
    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;

    /**
     * @param ProductCollectionFactory $collectionFactory
     */
    public function __construct(
        ProductCollectionFactory $collectionFactory,
        ResourceConnection $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    /**
     * Provide products collection filtered with store
     *
     * @param Store $store
     * @return Collection
     */
    public function getCollectionForStore(Store $store):Collection
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $collection->setStore($store);
        $collection->addAttributeToSelect(
            'gift_message_available'
        );
        $collection->addAttributeToSelect(
            'sku'
        );
        $collection->addStoreFilter();
        $collection->getSelect()->joinLeft(
            ['catalog_stock' => $this->resource->getTableName('cataloginventory_stock_status')],
            'e.entity_id = catalog_stock.product_id',
            ['catalog_stock.stock_status']
        )->where('catalog_stock.stock_status = 1');

        return $collection;
    }
}
