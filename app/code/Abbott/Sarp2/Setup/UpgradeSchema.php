<?php

namespace Abbott\Sarp2\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Aheadworks\Sarp2\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
      /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), "1.0.0", "<")) {
        
		$installer->getConnection()->addColumn(
                $installer->getTable('aw_sarp2_plan'),
                'is_progressive',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is Progressive'
                ]
            );
        }

        if (version_compare($context->getVersion(), "1.0.1", "<")) {

            $installer->getConnection()->addColumn(
                    $installer->getTable('aw_sarp2_profile_item'), 'original_price', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0.0000',
                'comment' => 'Product Original Price'
                    ]
            );

            $installer->getConnection()->addColumn(
                    $installer->getTable('aw_sarp2_profile_item'), 'base_price', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0.0000',
                'comment' => 'Product Base Price'
                    ]
            );
        }
		
		if (version_compare($context->getVersion(), "1.0.2", "<")) {
			$tableName = $installer->getTable('aw_sarp2_profile_item');
			if ($installer->getConnection()->tableColumnExists($tableName, 'base_price') === true) {
			$installer->getConnection()->changeColumn(
                    $tableName,
                    'base_price',
                    'base_original_price',
                    [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'length' => '12,4',
						'nullable' => false,
						'default' => '0.0000',
						'comment' => 'Product Base Original Price'
					]
                );
			}
		
		}
		
		if (version_compare($context->getVersion(), "1.0.3", "<")) {
        
		$installer->getConnection()->addColumn(
                $installer->getTable('aw_sarp2_plan'),
                'is_cancel_order',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Cancel Order Check'
                ]
            );
        }

        $installer->endSetup();
    }

}
