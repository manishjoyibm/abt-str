<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Setup\OrderAttributeEntityIncrement;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;

class MigrateEntityIdColumnData
{
    public const MATCH_PATTERN = '/AmorderattrMigrateEntityId\(([^\)]+)\,([^\)]+)\)/';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    public function __construct(
        ResourceConnection $resourceConnection,
        SelectFactory $selectFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->selectFactory = $selectFactory;
    }

    public function isApplicable(string $statement): bool
    {
        return (bool)preg_match(self::MATCH_PATTERN, $statement);
    }

    // Migrate sequence data to 'amasty_order_attribute_entity_increment' table.
    public function getCallback(ElementHistory $elementHistory): callable
    {
        /** @var Column $column */
        $column = $elementHistory->getNew();
        preg_match(self::MATCH_PATTERN, $column->getOnCreate(), $matches);
        return function () use ($column, $matches) {
            $tableName = $column->getTable()->getName();
            $tableMigrateFrom = $this->resourceConnection->getTableName($matches[1]);
            $columnMigrateFrom = $matches[2];
            $adapter = $this->resourceConnection->getConnection(
                $column->getTable()->getResource()
            );
            $select = $this->selectFactory->create($adapter);
            $select
                ->from(
                    $tableMigrateFrom,
                    [$column->getName() => $columnMigrateFrom]
                );
            $select->distinct(true);
            if ($adapter->isTableExists($tableMigrateFrom)) {
                $adapter->query(
                    $adapter->insertFromSelect(
                        $select,
                        $this->resourceConnection->getTableName($tableName)
                    )
                );
            }
        };
    }
}
