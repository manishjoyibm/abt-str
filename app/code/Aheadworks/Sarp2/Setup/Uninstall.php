<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class Uninstall
 *
 * @package Aheadworks\Sarp2\Setup
 */
class Uninstall implements UninstallInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $dataSetup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param ModuleDataSetupInterface $dataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        ModuleDataSetupInterface $dataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->dataSetup = $dataSetup;
    }

    /**
     * @inheritdoc
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this
            ->uninstallTables($installer)
            ->uninstallEavData()
            ->uninstallQuoteData()
            ->uninstallConfigData($installer);

        $installer->endSetup();
    }

    /**
     * Uninstall all module tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function uninstallTables(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $connection->dropTable($installer->getTable('aw_sarp2_subscription_option'));
        $connection->dropTable($installer->getTable('aw_sarp2_profile_address'));
        $connection->dropTable($installer->getTable('aw_sarp2_profile_definition'));
        $connection->dropTable($installer->getTable('aw_sarp2_profile_item'));
        $connection->dropTable($installer->getTable('aw_sarp2_profile_order'));
        $connection->dropTable($installer->getTable('aw_sarp2_core_notification'));
        $connection->dropTable($installer->getTable('aw_sarp2_core_schedule_item'));
        $connection->dropTable($installer->getTable('aw_sarp2_core_schedule'));
        $connection->dropTable($installer->getTable('aw_sarp2_plan_title'));
        $connection->dropTable($installer->getTable('aw_sarp2_payment_sampler'));
        $connection->dropTable($installer->getTable('aw_sarp2_profile'));
        $connection->dropTable($installer->getTable('aw_sarp2_payment_token'));
        $connection->dropTable($installer->getTable('aw_sarp2_plan'));
        $connection->dropTable($installer->getTable('aw_sarp2_plan_definition'));

        return $this;
    }

    /**
     * Uninstall EAV data
     *
     * @return $this
     */
    private function uninstallEavData()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->dataSetup]);
        $eavSetup->removeAttribute(Product::ENTITY, 'aw_sarp2_subscription_type');
        $eavSetup->removeAttribute(Product::ENTITY, 'aw_sarp2_subscription_options');

        return $this;
    }

    /**
     * Uninstall quote data
     *
     * @return $this
     */
    private function uninstallQuoteData()
    {
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $this->dataSetup]);
        $quoteSetup->removeQuoteTotals('quote', ['subtotal', 'grand_total']);
        $quoteSetup->removeQuoteTotals(
            'quote_address',
            [
                'subtotal',
                'subtotal_incl_tax',
                'tax_amount',
                'grand_total'
            ],
            ['initial']
        );
        $quoteSetup->removeQuoteTotals(
            'quote_address',
            [
                'subtotal',
                'subtotal_incl_tax',
                'shipping_amount',
                'shipping_amount_incl_tax',
                'tax_amount',
                'shipping_tax_amount',
                'grand_total'
            ],
            ['trial', 'regular']
        );

        foreach (['quote_item', 'quote_address_item'] as $entityTypeId) {
            $quoteSetup->removeQuoteTotals(
                $entityTypeId,
                [
                    'fee',
                    'fee_incl_tax',
                    'row_total',
                    'row_total_incl_tax',
                    'fee_tax_amount'
                ],
                ['initial']
            );
            $quoteSetup->removeQuoteTotals(
                $entityTypeId,
                ['fee_tax_percent'],
                ['initial'],
                ['aw_sarp']
            );
            $quoteSetup->removeQuoteTotals(
                $entityTypeId,
                [
                    'price',
                    'price_incl_tax',
                    'row_total',
                    'row_total_incl_tax',
                    'tax_amount'
                ],
                ['trial', 'regular']
            );
            $quoteSetup->removeQuoteTotals(
                $entityTypeId,
                ['tax_percent'],
                ['trial', 'regular'],
                ['aw_sarp']
            );

            $quoteSetup->removeAttribute($entityTypeId, 'aw_sarp_is_price_incl_initial_fee_amount');
            $quoteSetup->removeAttribute($entityTypeId, 'aw_sarp_is_price_incl_trial_amount');
            $quoteSetup->removeAttribute($entityTypeId, 'aw_sarp_is_price_incl_regular_amount');
        }

        return $this;
    }

    /**
     * Uninstall module data from config
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function uninstallConfigData(SchemaSetupInterface $installer)
    {
        $configTable = $installer->getTable('core_config_data');
        $installer->getConnection()->delete($configTable, "`path` LIKE 'aw_sarp2%'");
        return $this;
    }
}
