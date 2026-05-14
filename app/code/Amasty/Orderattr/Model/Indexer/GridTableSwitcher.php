<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Indexer;

use Amasty\Orderattr\Model\ResourceModel\Entity\Entity;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ResourceConnection;

class GridTableSwitcher
{
    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ResourceConnection $resource,
        ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->resource = $resource;
        $this->activeTableSwitcher = $activeTableSwitcher;
    }

    public function switchTables(): void
    {
        $tableName = $this->resource->getTableName(Entity::GRID_INDEXER_ID . '_flat');
        $connection = $this->resource->getConnection();
        if (!$connection->isTableExists($tableName)) {
            $connection->renameTable($tableName. '_replica', $tableName);

            return;
        }

        $this->activeTableSwitcher->switchTable($connection, [$tableName]);
    }
}
