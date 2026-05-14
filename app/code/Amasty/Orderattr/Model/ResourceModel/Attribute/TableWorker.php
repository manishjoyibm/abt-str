<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\ResourceModel\Attribute;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\Table\Strategy;

class TableWorker
{
    /**
     * @var Strategy
     */
    private $indexerTableStrategy;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        Strategy $indexerTableStrategy,
        ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->indexerTableStrategy = $indexerTableStrategy;
        $this->activeTableSwitcher = $activeTableSwitcher;
    }

    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    public function getTableName(string $tableName): string
    {
        return $this->resourceConnection->getTableName($tableName);
    }

    public function clearReplica(string $replicaTableName): void
    {
        $this->getConnection()->truncateTable(
            $this->getTableName($replicaTableName)
        );
    }

    public function createTemporaryTable(string $replicaTableName): void
    {
        $this->getConnection()->createTemporaryTableLike(
            $this->getIdxTable($replicaTableName),
            $this->getTableName($replicaTableName),
            true
        );

        $this->getConnection()->delete($this->getIdxTable($replicaTableName));
    }

    public function getIdxTable(string $replicaTableName): string
    {
        $this->indexerTableStrategy->setUseIdxTable(true);
        return $this->getTableName(
            $this->indexerTableStrategy->prepareTableName($replicaTableName)
        );
    }

    private function syncData(string $sourceTable, string $destinationTable, array $condition = []): void
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->getConnection()->delete($destinationTable, $condition);
            $this->insertFromTable(
                $this->getIdxTable($sourceTable),
                $destinationTable
            );
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    public function syncDataPartial(string $sourceTable, string $destTable, array $condition): void
    {
        $this->syncData($sourceTable, $this->getTableName($destTable), $condition);
    }

    public function syncDataFull(string $tableName): void
    {
        $this->syncData($tableName, $this->getTableName($tableName));
    }

    protected function insertFromTable(string $sourceTable, string $destTable): void
    {
        $sourceColumns = array_keys($this->getConnection()->describeTable($sourceTable));
        $targetColumns = array_keys($this->getConnection()->describeTable($destTable));

        $select = $this->getConnection()->select()->from($sourceTable, $sourceColumns);

        $this->getConnection()->query(
            $this->getConnection()->insertFromSelect(
                $select,
                $destTable,
                $targetColumns,
                AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
    }

    public function switchTables(string $mainTableName): void
    {
        $this->activeTableSwitcher->switchTable(
            $this->getConnection(),
            [$this->getTableName($mainTableName)]
        );
    }

    public function insert(string $tableName, array $data): void
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getTableName($this->getIdxTable($tableName)),
            $data
        );
    }
}
