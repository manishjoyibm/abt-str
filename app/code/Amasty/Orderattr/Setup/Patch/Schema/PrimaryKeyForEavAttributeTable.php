<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Setup\Patch\Schema;

use Amasty\Orderattr\Model\ResourceModel\Attribute\Attribute;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * compatibility with m2.4.2
 * Declarative Schema can't add autoincrement column if existing table hasn't primary key
 */
class PrimaryKeyForEavAttributeTable implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $setup;

    public function __construct(
        SchemaSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    public function apply(): void
    {
        $this->setup->getConnection()->addColumn(
            $this->setup->getTable(Attribute::TABLE_NAME),
            'id',
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Id',
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        );
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
