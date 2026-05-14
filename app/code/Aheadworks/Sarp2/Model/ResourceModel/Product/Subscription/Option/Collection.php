<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option;

use Aheadworks\Sarp2\Model\Product\Subscription\Option;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option as OptionResource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'option_id';

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Option::class, OptionResource::class);
    }

    /**
     * Add product filter to collection
     *
     * @param int $productId
     * @return $this
     */
    public function addProductFilter($productId)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $this->getSelect()
            ->joinLeft(
                ['product_entity' => $this->getTable('catalog_product_entity')],
                'product_entity.' . $linkField . ' = main_table.product_id',
                []
            )
            ->where('product_entity.entity_id = ?', $productId);
        return $this;
    }

    /**
     * Add store filter to collection
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $connection = $this->getConnection();
        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        $select = $this->getSelect();
        $select
            ->where('main_table.website_id IN (?)', [0, $websiteId])
            ->joinLeft(
                ['plan_title_store' => $this->getTable('aw_sarp2_plan_title')],
                $connection->quoteInto(
                    'plan_title_store.plan_id = main_table.plan_id AND plan_title_store.store_id = ?',
                    $storeId
                ),
                []
            )
            ->joinLeft(
                ['plan_title_default' => $this->getTable('aw_sarp2_plan_title')],
                $connection->quoteInto(
                    'plan_title_default.plan_id = main_table.plan_id AND plan_title_default.store_id = ?',
                    0
                ),
                []
            );

        $columns = $select->getPart(Select::COLUMNS);
        $columns[] = [
            'main_table',
            new \Zend_Db_Expr(
                "IFNULL(plan_title_store.title, plan_title_default.title)"
            ),
            'frontend_title'
        ];
        $select->setPart(Select::COLUMNS, $columns);

        return $this;
    }
}
