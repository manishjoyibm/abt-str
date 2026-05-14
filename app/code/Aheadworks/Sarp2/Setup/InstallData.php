<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Setup;

use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions as BackendSubscriptionOptions;
use Aheadworks\Sarp2\Model\Product\Attribute\Source\SubscriptionType as SubscriptionTypeSource;
use Aheadworks\Sarp2\Model\Product\Type\Plugin\Config;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\SequenceConfig;
use Aheadworks\Sarp2\Setup\Updater\Data\Updater;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\SalesSequence\Model\Builder as SequenceBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Class InstallData
 * @package Aheadworks\Sarp2\Setup
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
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
     * @var ConfigInterface
     */
    private $productTypeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SequenceConfig
     */
    private $sequenceConfig;

    /**
     * @var SequenceBuilder
     */
    private $sequenceBuilder;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param ConfigInterface $productTypeConfig
     * @param StoreManagerInterface $storeManager
     * @param SequenceConfig $sequenceConfig
     * @param SequenceBuilder $sequenceBuilder
     * @param Updater $updater
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        ConfigInterface $productTypeConfig,
        StoreManagerInterface $storeManager,
        SequenceConfig $sequenceConfig,
        SequenceBuilder $sequenceBuilder,
        Updater $updater
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->productTypeConfig = $productTypeConfig;
        $this->storeManager = $storeManager;
        $this->sequenceConfig = $sequenceConfig;
        $this->sequenceBuilder = $sequenceBuilder;
        $this->updater = $updater;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $applyTo = implode(
            ',',
            $this->productTypeConfig->filter(Config::SUPPORTED_CUSTOM_ATTR_CODE, true)
        );

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'aw_sarp2_subscription_type',
            [
                'type' => 'int',
                'group' => 'Sarp2: Subscription Configuration',
                'label' => 'Subscription',
                'input' => 'select',
                'sort_order' => 1,
                'source' => SubscriptionTypeSource::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => SubscriptionTypeSource::NO,
                'apply_to' => $applyTo,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true
            ]
        )->addAttribute(
            Product::ENTITY,
            'aw_sarp2_subscription_options',
            [
                'type' => 'decimal',
                'group' => 'Sarp2: Subscription Configuration',
                'label' => 'Subscription Options',
                'input' => 'text',
                'backend' => BackendSubscriptionOptions::class,
                'sort_order' => 2,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'apply_to' => $applyTo,
                'visible_on_front' => false
            ]
        );

        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(
            ['resourceName' => 'quote_setup', 'setup' => $setup]
        );

        $quoteSetup->addQuoteTotals('quote', ['subtotal', 'grand_total']);
        $quoteSetup->addQuoteTotals(
            'quote_address',
            [
                'subtotal',
                'subtotal_incl_tax',
                'tax_amount',
                'grand_total'
            ],
            ['initial']
        );
        $quoteSetup->addQuoteTotals(
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
            $quoteSetup->addQuoteTotals(
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
            $quoteSetup->addQuoteTotals(
                $entityTypeId,
                ['fee_tax_percent'],
                ['initial'],
                ['aw_sarp']
            );
            $quoteSetup->addQuoteTotals(
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
            $quoteSetup->addQuoteTotals(
                $entityTypeId,
                ['tax_percent'],
                ['trial', 'regular'],
                ['aw_sarp']
            );

            $quoteSetup->addAttribute(
                $entityTypeId,
                'aw_sarp_is_price_incl_initial_fee_amount',
                ['type' => Table::TYPE_BOOLEAN]
            );
            $quoteSetup->addAttribute(
                $entityTypeId,
                'aw_sarp_is_price_incl_trial_amount',
                ['type' => Table::TYPE_BOOLEAN]
            );
            $quoteSetup->addAttribute(
                $entityTypeId,
                'aw_sarp_is_price_incl_regular_amount',
                ['type' => Table::TYPE_BOOLEAN]
            );
        }

        $this->createProfileSequence();
        $this->updater->updateTo220($setup);
    }

    /**
     * Create profile sequence
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function createProfileSequence()
    {
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->sequenceBuilder->setPrefix($this->sequenceConfig->get('prefix'))
                ->setSuffix($this->sequenceConfig->get('suffix'))
                ->setStartValue($this->sequenceConfig->get('startValue'))
                ->setStoreId($store->getId())
                ->setStep($this->sequenceConfig->get('step'))
                ->setWarningValue($this->sequenceConfig->get('warningValue'))
                ->setMaxValue($this->sequenceConfig->get('maxValue'))
                ->setEntityType(Profile::ENTITY)
                ->create();
        }
    }
}
