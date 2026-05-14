<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Setup;

use Aheadworks\Sarp2\Setup\Updater\Shema\Updater;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Aheadworks\SaSarp2rp\Setup
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @param MetadataPool $metadataPool
     * @param Updater $updater
     */
    public function __construct(
        MetadataPool $metadataPool,
        Updater $updater
    ) {
        $this->metadataPool = $metadataPool;
        $this->updater = $updater;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'aw_sarp2_plan'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_plan'))
            ->addColumn(
                'plan_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Plan Id'
            )->addColumn(
                'definition_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Definition Id'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )->addColumn(
                'regular_price_pattern_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '100.0000'],
                'Regular price percentage of product price'
            )->addColumn(
                'trial_price_pattern_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '100.0000'],
                'Trial price percentage of product price'
            )->addColumn(
                'price_rounding',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Price Rounding'
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_plan',
                    'definition_id',
                    'aw_sarp2_plan_definition',
                    'definition_id'
                ),
                'definition_id',
                $installer->getTable('aw_sarp2_plan_definition'),
                'definition_id',
                Table::ACTION_CASCADE
            )->setComment('Plan');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_plan_definition'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_plan_definition'))
            ->addColumn(
                'definition_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Plan Definition Id'
            )->addColumn(
                'billing_period',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Billing Period'
            )->addColumn(
                'billing_frequency',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Billing Frequency'
            )->addColumn(
                'total_billing_cycles',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Total Billing Cycles'
            )->addColumn(
                'start_date_type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Start Sate Type'
            )->addColumn(
                'start_date_day_of_month',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Day Of Month Of Start Date'
            )->addColumn(
                'is_initial_fee_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Initial Fee Enabled'
            )->addColumn(
                'is_trial_period_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Trial Period Enabled'
            )->addColumn(
                'trial_total_billing_cycles',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Trial Total Billing Cycles'
            )->setComment('Plan Definition');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_plan_title'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_plan_title'))
            ->addColumn(
                'plan_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Plan Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Title'
            )->addIndex(
                $installer->getIdxName('aw_sarp2_plan_title', ['plan_id']),
                ['plan_id']
            )->addIndex(
                $installer->getIdxName('aw_sarp2_plan_title', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_plan_title',
                    'plan_id',
                    'aw_sarp2_plan',
                    'plan_id'
                ),
                'plan_id',
                $installer->getTable('aw_sarp2_plan'),
                'plan_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_sarp2_plan_title', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Plan Title');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_subscription_option'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_subscription_option'))
            ->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Id'
            )->addColumn(
                'plan_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Plan Id'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product ID'
            )->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website ID'
            )->addColumn(
                'initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Initial Fee'
            )->addColumn(
                'trial_price',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Price'
            )->addColumn(
                'regular_price',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Regular Price'
            )->addColumn(
                'is_auto_trial_price',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '1'],
                'Is Auto Trial Price'
            )->addColumn(
                'is_auto_regular_price',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '1'],
                'Is Auto Regular Price'
            )->addIndex(
                $installer->getIdxName('aw_sarp2_subscription_option', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('aw_sarp2_subscription_option', ['website_id']),
                ['website_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_subscription_option',
                    'plan_id',
                    'aw_sarp2_plan',
                    'plan_id'
                ),
                'plan_id',
                $installer->getTable('aw_sarp2_plan'),
                'plan_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_subscription_option',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                $this->metadataPool->getMetadata(ProductInterface::class)
                    ->getLinkField(),
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_subscription_option',
                    'website_id',
                    'store_website',
                    'website_id'
                ),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )->setComment('Subscription Option Title');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_profile'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_profile'))
            ->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Profile Id'
            )->addColumn(
                'increment_id',
                Table::TYPE_TEXT,
                32,
                [],
                'Increment Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store Id'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_UPDATE],
                'Updated At'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'plan_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Plan Id'
            )->addColumn(
                'plan_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Plan Name'
            )->addColumn(
                'is_virtual',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Virtual'
            )->addColumn(
                'plan_definition_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Plan Definition Id'
            )->addColumn(
                'start_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Start Date'
            )->addColumn(
                'items_qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Items Qty'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )->addColumn(
                'customer_tax_class_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Tax Class Id'
            )->addColumn(
                'customer_group_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Customer Group Id'
            )->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Email'
            )->addColumn(
                'customer_dob',
                Table::TYPE_DATETIME,
                null,
                [],
                'Customer Dob'
            )->addColumn(
                'customer_fullname',
                Table::TYPE_TEXT,
                32,
                [],
                'Customer Full Name'
            )->addColumn(
                'customer_prefix',
                Table::TYPE_TEXT,
                32,
                [],
                'Customer Prefix'
            )->addColumn(
                'customer_firstname',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Firstname'
            )->addColumn(
                'customer_middlename',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Middlename'
            )->addColumn(
                'customer_lastname',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Lastname'
            )->addColumn(
                'customer_suffix',
                Table::TYPE_TEXT,
                32,
                [],
                'Customer Suffix'
            )->addColumn(
                'customer_is_guest',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true],
                'Customer Is Guest'
            )->addColumn(
                'checkout_shipping_method',
                Table::TYPE_TEXT,
                40,
                [],
                'Checkout Shipping Method'
            )->addColumn(
                'checkout_shipping_description',
                Table::TYPE_TEXT,
                255,
                [],
                'Checkout Shipping Description'
            )->addColumn(
                'trial_shipping_method',
                Table::TYPE_TEXT,
                40,
                [],
                'Trial Shipping Method'
            )->addColumn(
                'trial_shipping_description',
                Table::TYPE_TEXT,
                255,
                [],
                'Trial Shipping Description'
            )->addColumn(
                'regular_shipping_method',
                Table::TYPE_TEXT,
                40,
                [],
                'Regular Shipping Method'
            )->addColumn(
                'regular_shipping_description',
                Table::TYPE_TEXT,
                255,
                [],
                'Regular Shipping Description'
            )->addColumn(
                'global_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Global Currency Code'
            )->addColumn(
                'base_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Base Currency Code'
            )->addColumn(
                'profile_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Profile Currency Code'
            )->addColumn(
                'base_to_global_rate',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base To Global Rate'
            )->addColumn(
                'base_to_profile_rate',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base To Profile Rate'
            )->addColumn(
                'initial_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Initial Subtotal'
            )->addColumn(
                'base_initial_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Initial Subtotal'
            )->addColumn(
                'initial_subtotal_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Initial Subtotal Incl Tax'
            )->addColumn(
                'base_initial_subtotal_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Initial Subtotal Incl Tax'
            )->addColumn(
                'initial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Initial Tax Amount'
            )->addColumn(
                'base_initial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Initial Tax Amount'
            )->addColumn(
                'initial_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Initial Grand Total'
            )->addColumn(
                'base_initial_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Initial Grand Total'
            )->addColumn(
                'trial_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Subtotal'
            )->addColumn(
                'base_trial_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Trial Subtotal'
            )->addColumn(
                'trial_subtotal_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Subtotal Incl Tax'
            )->addColumn(
                'base_trial_subtotal_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Trial Subtotal Incl Tax'
            )->addColumn(
                'trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Tax Amount'
            )->addColumn(
                'base_trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Trial Tax Amount'
            )->addColumn(
                'trial_shipping_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Shipping Amount'
            )->addColumn(
                'base_trial_shipping_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Trial Shipping Amount'
            )->addColumn(
                'trial_shipping_amount_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Shipping Amount Incl Tax'
            )->addColumn(
                'base_trial_shipping_amount_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Trial Shipping Amount Incl Tax'
            )->addColumn(
                'trial_shipping_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Shipping Tax Amount'
            )->addColumn(
                'base_trial_shipping_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Trial Shipping Tax Amount'
            )->addColumn(
                'trial_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Trial Grand Total'
            )->addColumn(
                'base_trial_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Trial Grand Total'
            )->addColumn(
                'regular_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Regular Subtotal'
            )->addColumn(
                'base_regular_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Regular Subtotal'
            )->addColumn(
                'regular_subtotal_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Regular Subtotal Incl Tax'
            )->addColumn(
                'base_regular_subtotal_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Regular Subtotal Incl Tax'
            )->addColumn(
                'regular_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Regular Tax Amount'
            )->addColumn(
                'base_regular_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Regular Tax Amount'
            )->addColumn(
                'regular_shipping_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Regular Shipping Amount'
            )->addColumn(
                'base_regular_shipping_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Regular Shipping Amount'
            )->addColumn(
                'regular_shipping_amount_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Regular Shipping Amount Incl Tax'
            )->addColumn(
                'base_regular_shipping_amount_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Regular Shipping Amount Incl Tax'
            )->addColumn(
                'regular_shipping_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Regular Shipping Tax Amount'
            )->addColumn(
                'base_regular_shipping_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Regular Shipping Tax Amount'
            )->addColumn(
                'regular_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Regular Grand Total'
            )->addColumn(
                'base_regular_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Regular Grand Total'
            )->addColumn(
                'payment_method',
                Table::TYPE_TEXT,
                255,
                [],
                'Payment Method'
            )->addColumn(
                'payment_token_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Payment Token Id'
            )->addColumn(
                'last_order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Last Order Id'
            )->addColumn(
                'last_order_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => true, 'default' => null],
                'Last Order Date'
            )->addColumn(
                'remote_ip',
                Table::TYPE_TEXT,
                32,
                [],
                'Remote Ip'
            )->addIndex(
                $installer->getIdxName('aw_sarp2_profile', ['customer_id']),
                ['customer_id']
            )->addForeignKey(
                $installer->getFkName('aw_sarp2_profile', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_sarp2_profile', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_SET_NULL
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_profile',
                    'payment_token_id',
                    'aw_sarp2_payment_token',
                    'token_id'
                ),
                'payment_token_id',
                $installer->getTable('aw_sarp2_payment_token'),
                'token_id',
                Table::ACTION_SET_DEFAULT
            )->setComment('Recurring Profile');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_profile_item'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_profile_item'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Item Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Profile Id'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_UPDATE],
                'Updated At'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product Id'
            )->addColumn(
                'product_type',
                Table::TYPE_TEXT,
                255,
                [],
                'Product Type'
            )->addColumn(
                'product_options',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Product Options Serialized'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Store Id'
            )->addColumn(
                'parent_item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Parent Item Id'
            )->addColumn(
                'is_virtual',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Is Virtual'
            )->addColumn(
                'sku',
                Table::TYPE_TEXT,
                255,
                [],
                'Sku'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                [],
                'Name'
            )->addColumn(
                'description',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Description'
            )->addColumn(
                'is_qty_decimal',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Is Qty Decimal'
            )->addColumn(
                'weight',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Weight'
            )->addColumn(
                'qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Qty'
            )->addColumn(
                'is_free_shipping',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Free Shipping Flag'
            )->addColumn(
                'initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Initial Fee'
            )->addColumn(
                'base_initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Initial Fee'
            )->addColumn(
                'initial_fee_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Initial Fee Incl Tax'
            )->addColumn(
                'base_initial_fee_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Initial Fee Incl Tax'
            )->addColumn(
                'initial_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Initial Row Total'
            )->addColumn(
                'base_initial_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Initial Row Total'
            )->addColumn(
                'initial_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Initial Row Total Incl Tax'
            )->addColumn(
                'base_initial_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Initial Row Total Incl Tax'
            )->addColumn(
                'initial_fee_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Initial Fee Tax Amount'
            )->addColumn(
                'base_initial_fee_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Initial Fee Tax Amount'
            )->addColumn(
                'initial_fee_tax_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Initial Fee Tax Percent'
            )->addColumn(
                'trial_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Price'
            )->addColumn(
                'base_trial_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Price'
            )->addColumn(
                'trial_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Price Incl Tax'
            )->addColumn(
                'base_trial_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Price Incl Tax'
            )->addColumn(
                'trial_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Row Total'
            )->addColumn(
                'base_trial_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Row Total'
            )->addColumn(
                'trial_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Row Total Incl Tax'
            )->addColumn(
                'base_trial_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Row Total Incl Tax'
            )->addColumn(
                'trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Trial Tax Amount'
            )->addColumn(
                'base_trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Trial Tax Amount'
            )->addColumn(
                'trial_tax_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Trial Tax Percent'
            )->addColumn(
                'regular_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Regular Price'
            )->addColumn(
                'base_regular_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Regular Price'
            )->addColumn(
                'regular_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Regular Price Incl Tax'
            )->addColumn(
                'base_regular_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Regular Price Incl Tax'
            )->addColumn(
                'regular_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Regular Row Total'
            )->addColumn(
                'base_regular_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Regular Row Total'
            )->addColumn(
                'regular_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Regular Row Total Incl Tax'
            )->addColumn(
                'base_regular_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Regular Row Total Incl Tax'
            )->addColumn(
                'regular_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Regular Tax Amount'
            )->addColumn(
                'base_regular_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Regular Tax Amount'
            )->addColumn(
                'regular_tax_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Regular Tax Percent'
            )->addColumn(
                'row_weight',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Row Weight'
            )->addIndex(
                $installer->getIdxName('aw_sarp2_profile_item', ['parent_item_id']),
                ['parent_item_id']
            )->addIndex(
                $installer->getIdxName('aw_sarp2_profile_item', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('aw_sarp2_profile_item', ['profile_id']),
                ['profile_id']
            )->addIndex(
                $installer->getIdxName('aw_sarp2_profile_item', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_profile_item',
                    'parent_item_id',
                    'aw_sarp2_profile_item',
                    'item_id'
                ),
                'parent_item_id',
                $installer->getTable('aw_sarp2_profile_item'),
                'item_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_sarp2_profile_item', 'profile_id', 'aw_sarp2_profile', 'profile_id'),
                'profile_id',
                $installer->getTable('aw_sarp2_profile'),
                'profile_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_sarp2_profile_item', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_SET_NULL
            )->setComment('Recurring Profile Item');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_profile_address'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_profile_address'))
            ->addColumn(
                'address_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Address Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Quote Id'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_UPDATE],
                'Updated At'
            )->addColumn(
                'customer_address_id',
                Table::TYPE_INTEGER,
                null,
                [],
                'Customer Address Id'
            )->addColumn(
                'quote_address_id',
                Table::TYPE_INTEGER,
                null,
                [],
                'Quote Address Id'
            )->addColumn(
                'region_id',
                Table::TYPE_INTEGER,
                null,
                [],
                'Region Id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )->addColumn(
                'fax',
                Table::TYPE_TEXT,
                255,
                [],
                'Fax'
            )->addColumn(
                'region',
                Table::TYPE_TEXT,
                255,
                [],
                'Region'
            )->addColumn(
                'postcode',
                Table::TYPE_TEXT,
                255,
                [],
                'Postcode'
            )->addColumn(
                'lastname',
                Table::TYPE_TEXT,
                255,
                [],
                'Lastname'
            )->addColumn(
                'street',
                Table::TYPE_TEXT,
                255,
                [],
                'Street'
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                255,
                [],
                'City'
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                255,
                [],
                'Email'
            )->addColumn(
                'telephone',
                Table::TYPE_TEXT,
                255,
                [],
                'Phone Number'
            )->addColumn(
                'country_id',
                Table::TYPE_TEXT,
                2,
                [],
                'Country Id'
            )->addColumn(
                'firstname',
                Table::TYPE_TEXT,
                255,
                [],
                'Firstname'
            )->addColumn(
                'address_type',
                Table::TYPE_TEXT,
                255,
                [],
                'Address Type'
            )->addColumn(
                'prefix',
                Table::TYPE_TEXT,
                255,
                [],
                'Prefix'
            )->addColumn(
                'middlename',
                Table::TYPE_TEXT,
                255,
                [],
                'Middlename'
            )->addColumn(
                'suffix',
                Table::TYPE_TEXT,
                255,
                [],
                'Suffix'
            )->addColumn(
                'company',
                Table::TYPE_TEXT,
                255,
                [],
                'Company'
            )->addColumn(
                'weight',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Weight'
            )->addColumn(
                'is_free_shipping',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Free Shipping Flag'
            )->addIndex(
                $installer->getIdxName('aw_sarp2_profile_address', ['profile_id']),
                ['profile_id']
            )->addIndex(
                $installer->getIdxName('aw_sarp2_profile_address', ['customer_id']),
                ['customer_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_profile_address',
                    'profile_id',
                    'aw_sarp2_profile',
                    'profile_id'
                ),
                'profile_id',
                $installer->getTable('aw_sarp2_profile'),
                'profile_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_sarp2_profile_address', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_SET_NULL
            )->setComment('Recurring Profile Address');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_payment_token'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_payment_token'))
            ->addColumn(
                'token_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Token Id'
            )->addColumn(
                'payment_method',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Payment Method Code'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Token Type'
            )->addColumn(
                'token_value',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Token Value'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'expires_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Expires At'
            )->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'dafault' => true],
                'Is Active Flag'
            )->setComment('Payment Token');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_profile_order'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_profile_order'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Order Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Profile Id'
            )->addIndex(
                $installer->getIdxName('aw_sarp2_profile_order', ['profile_id']),
                ['profile_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_profile_order',
                    'profile_id',
                    'aw_sarp2_profile',
                    'profile_id'
                ),
                'profile_id',
                $installer->getTable('aw_sarp2_profile'),
                'profile_id',
                Table::ACTION_CASCADE
            )->setComment('Recurring Profile Order');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_core_schedule'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_sarp2_core_schedule'))
            ->addColumn(
                'schedule_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Core Schedule Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Profile Id'
            )->addColumn(
                'period',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Payments Period'
            )->addColumn(
                'frequency',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Payments Frequency'
            )->addColumn(
                'is_initial_paid',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Initial Payment Paid'
            )->addColumn(
                'trial_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Trial Payments Count'
            )->addColumn(
                'trial_total_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Trial Payments Total Count'
            )->addColumn(
                'regular_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Regular Payments Count'
            )->addColumn(
                'regular_total_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Regular Payments Total Count'
            )->addColumn(
                'payment_data',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Payment Data'
            )->addColumn(
                'is_reactivated',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Reactivated'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addIndex(
                $setup->getIdxName('aw_sarp2_core_schedule', ['profile_id']),
                ['profile_id']
            )->addForeignKey(
                $setup->getFkName(
                    'aw_sarp2_core_schedule',
                    'profile_id',
                    'aw_sarp2_profile',
                    'profile_id'
                ),
                'profile_id',
                $setup->getTable('aw_sarp2_profile'),
                'profile_id',
                Table::ACTION_CASCADE
            )->setComment('Core Schedule');
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_core_schedule_item'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_sarp2_core_schedule_item'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Core Schedule Item Id'
            )->addColumn(
                'parent_item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Core Schedule Parent Item'
            )->addColumn(
                'schedule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Schedule Id'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Schedule Item Type'
            )->addColumn(
                'payment_period',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Payment Period'
            )->addColumn(
                'payment_status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Payment Status'
            )->addColumn(
                'scheduled_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false],
                'Scheduled At'
            )->addColumn(
                'paid_at',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Paid At'
            )->addColumn(
                'retry_at',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Retry At'
            )->addColumn(
                'retries_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Retries Count'
            )->addColumn(
                'total_scheduled',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Total Scheduled Amount'
            )->addColumn(
                'base_total_scheduled',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Total Scheduled Amount'
            )->addColumn(
                'total_paid',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Total Paid Amount'
            )->addColumn(
                'base_total_paid',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Total Paid Amount'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Order Id'
            )->addIndex(
                $installer->getIdxName('aw_sarp2_core_schedule_item', ['parent_item_id']),
                ['parent_item_id']
            )->addForeignKey(
                $setup->getFkName(
                    'aw_sarp2_core_schedule_item',
                    'schedule_id',
                    'aw_sarp2_core_schedule',
                    'schedule_id'
                ),
                'schedule_id',
                $setup->getTable('aw_sarp2_core_schedule'),
                'schedule_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp2_core_schedule_item',
                    'parent_item_id',
                    'aw_sarp2_core_schedule_item',
                    'item_id'
                ),
                'parent_item_id',
                $installer->getTable('aw_sarp2_core_schedule_item'),
                'item_id',
                Table::ACTION_CASCADE
            )->setComment('Core Schedule Item');
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp2_core_notification'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_sarp2_core_notification'))
            ->addColumn(
                'notification_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Notification Id'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Notification Type'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Notification Status'
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                128,
                [],
                'Email'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                32,
                [],
                'Name'
            )->addColumn(
                'scheduled_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false],
                'Scheduled At'
            )->addColumn(
                'send_at',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Send At'
            )->addColumn(
                'notification_data',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Notification Data'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Profile Id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Order Id'
            )->addForeignKey(
                $installer->getFkName('aw_sarp2_core_notification', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    'aw_sarp2_core_notification',
                    'profile_id',
                    'aw_sarp2_profile',
                    'profile_id'
                ),
                'profile_id',
                $setup->getTable('aw_sarp2_profile'),
                'profile_id',
                Table::ACTION_CASCADE
            )->setComment('Core Notification');
        $setup->getConnection()->createTable($table);

        $this->updater
            ->install220($installer)
            ->install230($installer)
            ->install240($installer)
            ->install250($installer);

        $installer->endSetup();
    }
}
