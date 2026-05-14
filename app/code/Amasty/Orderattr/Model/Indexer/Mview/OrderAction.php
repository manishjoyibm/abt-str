<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Indexer\Mview;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Mview\ActionInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Amasty\Orderattr\Model\ResourceModel\Entity\Entity;

class OrderAction implements ActionInterface
{
    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    public function __construct(IndexerInterfaceFactory $indexerFactory)
    {
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     */
    public function execute($ids)
    {
        /** @var IndexerInterface $indexer */
        $indexer = $this->indexerFactory->create()->load(Entity::GRID_INDEXER_ID);
        $indexer->reindexList($ids);
    }
}
