<?php


namespace Abbott\Subscriptionhistory\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class Constraint implements SchemaPatchInterface
{

    private $resourceConnection;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SchemaSetupInterface $resourceConnection
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConnection = $resourceConnection;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $installer = $this->resourceConnection;
        $table = $this->resourceConnection->getConnection();
        $table->addIndex(
            $installer->getTable('aw_sarp2_profile'),
            $installer->getIdxName('aw_sarp2_profile', ['profile_id']),
            ['profile_id']
        );

        $table->addIndex(
            $installer->getTable('customer_entity'),
            $installer->getIdxName('customer_entity', ['entity_id']),
            ['entity_id']
        );

        $table->addForeignKey(
            $installer->getFkName('aw_sarp2_subscription_history', 'profile_id', 'aw_sarp2_profile', 'profile_id'),
            'aw_sarp2_subscription_history',
            'profile_id',
            'aw_sarp2_profile',
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $table->addForeignKey(
            $installer->getFkName('aw_sarp2_subscription_history', 'customer_id', 'customer_entity', 'entity_id'),
            'aw_sarp2_subscription_history',
            'customer_id',
            'customer_entity',
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $this->moduleDataSetup->endSetup();
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
