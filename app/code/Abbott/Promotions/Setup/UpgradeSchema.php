<?php

namespace Abbott\Promotions\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $installer->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'literature_sku',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'literature_sku'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'sampling_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 1,
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'sampling_status'
                ]
            );
        }
        $installer->endSetup();
    }
}
