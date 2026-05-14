<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Setup;

use Amasty\Orderattr\Model\ResourceModel\Attribute\Attribute as AttributeResource;
use Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\Relation as RelationResource;
use Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails as RelationDetailsResource;
use Amasty\Orderattr\Model\ResourceModel\Entity\Entity as EntityResource;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    private const TABLE_NAMES = [
        EntityResource::TABLE_NAME,
        AttributeResource::TABLE_NAME,
        RelationResource::TABLE_NAME,
        RelationDetailsResource::TABLE_NAME,
        AttributeResource::CUSTOMER_GROUP_TABLE_NAME,
        AttributeResource::STORE_TABLE_NAME,
        'amasty_order_attribute_entity_int',
        'amasty_order_attribute_entity_decimal',
        'amasty_order_attribute_entity_datetime',
        'amasty_order_attribute_entity_text',
        'amasty_order_attribute_entity_varchar',
        AttributeResource::SHIPPING_METHODS_TABLE_NAME,
        AttributeResource::TOOLTIP_TABLE_NAME
    ];

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        foreach (self::TABLE_NAMES as $tableName) {
            $connection->dropTable($setup->getTable($tableName));
        }
    }
}
