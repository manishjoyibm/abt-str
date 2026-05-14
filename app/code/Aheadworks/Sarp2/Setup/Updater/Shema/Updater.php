<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Setup\Updater\Shema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class Updater
 * @package Aheadworks\Sarp2\Setup\Updater\Shema
 */
class Updater
{
    /**
     * Upgrade to version 2.2.0
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function upgradeTo220(SchemaSetupInterface $installer)
    {
        $this
            ->addMembershipFields($installer)
            ->addUpcomingBillingEmailOffsetField($installer)
            ->addProfileDefinition($installer)
            ->copyDefinitionFromPlanToProfile($installer);

        return $this;
    }

    /**
     * Install version 2.2.0
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function install220(SchemaSetupInterface $installer)
    {
        $this
            ->addMembershipFields($installer)
            ->addUpcomingBillingEmailOffsetField($installer)
            ->addProfileDefinition($installer);

        return $this;
    }

    /**
     * Upgrade to version 2.3.0
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function upgradeTo230(SchemaSetupInterface $installer)
    {
        $this
            ->addPaymentTokenDetails($installer)
            ->createPaymentSamplerTable($installer)
        ;

        return $this;
    }

    /**
     * Install version 2.3.0
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function install230(SchemaSetupInterface $installer)
    {
        $this
            ->addPaymentTokenDetails($installer)
            ->createPaymentSamplerTable($installer)
        ;

        return $this;
    }

    /**
     * Upgrade to version 2.4.0
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function upgradeTo240(SchemaSetupInterface $installer)
    {
        $this->addSortOrder($installer);

        return $this;
    }

    /**
     * Install version 2.4.0
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function install240(SchemaSetupInterface $installer)
    {
        $this->addSortOrder($installer);

        return $this;
    }

    /**
     * Upgrade to version 2.5.0
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function upgradeTo250(SchemaSetupInterface $installer)
    {
        $this->changePaymentTokenColumn($installer);

        return $this;
    }

    /**
     * Install version 2.5.0
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function install250(SchemaSetupInterface $installer)
    {
        $this->changePaymentTokenColumn($installer);

        return $this;
    }

    /**
     * Add membership fields
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addMembershipFields($installer)
    {
        $connection = $installer->getConnection();

        $connection->addColumn(
            $installer->getTable('aw_sarp2_plan_definition'),
            'is_membership_model_enabled',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => false,
                'comment' => 'Is Membership Model'
            ]
        );
        $connection->addColumn(
            $installer->getTable('aw_sarp2_core_schedule'),
            'is_membership_model',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => false,
                'comment' => 'Is Membership Model'
            ]
        );

        return $this;
    }

    /**
     * Add upcoming billing email offset field
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addUpcomingBillingEmailOffsetField($installer)
    {
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('aw_sarp2_plan_definition'),
            'upcoming_billing_email_offset',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'unsigned' => true,
                'comment' => 'Upcoming billing email offset'
            ]
        );

        return $this;
    }

    /**
     * Add profile definition
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addProfileDefinition($installer)
    {
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('aw_sarp2_profile'),
            'profile_definition_id',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Profile Definition Id',
                'after'   => 'plan_definition_id',
            ]
        );

        /**
         * Create table 'aw_sarp2_profile_definition'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_profile_definition'))
            ->addColumn(
                'definition_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Profile Definition Id'
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
            )->addColumn(
                'is_membership_model_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => false],
                'Is Membership Model'
            )->addColumn(
                'upcoming_billing_email_offset',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Upcoming billing email offset'
            )->setComment('Profile Definition');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Copy definition from plan to profile
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    public function copyDefinitionFromPlanToProfile($installer)
    {
        $connection = $installer->getConnection();
        $select = $connection->select()->from(
            $installer->getTable('aw_sarp2_profile'),
            ['plan_definition_id']
        )->group('plan_definition_id');
        $planDefinitionIds = $connection->fetchAssoc($select);

        $select = $connection->select()->from(
            $installer->getTable('aw_sarp2_plan_definition')
        )->where('definition_id IN (?)', $planDefinitionIds);
        $planDefinitions = $connection->fetchAssoc($select);

        $select = $connection->select()->from(
            $installer->getTable('aw_sarp2_profile'),
            ['profile_id', 'plan_definition_id']
        );
        $profileDefinitions = $connection->fetchAssoc($select);

        foreach ($profileDefinitions as $profile) {
            $planDefinitionId = $profile['plan_definition_id'];
            $profileId = $profile['profile_id'];
            if (!isset($planDefinitions[$planDefinitionId])) {
                continue;
            }

            $planDefinitionData = $planDefinitions[$planDefinitionId];
            unset($planDefinitionData['definition_id']);

            $connection->insert(
                $installer->getTable('aw_sarp2_profile_definition'),
                $planDefinitionData
            );
            $profileDefinitionId = $connection->lastInsertId('aw_sarp2_profile_definition');

            $connection->update(
                $installer->getTable('aw_sarp2_profile'),
                ['profile_definition_id' => $profileDefinitionId],
                ['profile_id = ?' => $profileId]
            );
        }
        return $this;
    }

    /**
     * Create payment sampler table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function createPaymentSamplerTable($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp2_payment_sampler'))
            ->addColumn(
                'sampler_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Payment Sampler Id'
            )->addColumn(
                'method',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Payment Method Code'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'last_transaction_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Last Transaction Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customer Id'
            )->addColumn(
                'amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Sample Amount'
            )->addColumn(
                'amount_placed',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Placed Amount'
            )->addColumn(
                'amount_reverted',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Reverted Amount'
            )->addColumn(
                'currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Currency Code'
            )->addColumn(
                'remote_ip',
                Table::TYPE_TEXT,
                32,
                [],
                'Remote Ip'
            )->addColumn(
                'additional_information',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Additional Information'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Profile Id'
            )->setComment('Payment Sampler');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add payment token details field
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addPaymentTokenDetails($installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('aw_sarp2_payment_token'),
            'details',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'comment' => 'Token Details'
            ]
        );
        return $this;
    }

    /**
     * Add plan sort order
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addSortOrder($installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('aw_sarp2_plan'),
            'sort_order',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'after'   => 'name',
                'comment' => 'Sort Order'
            ]
        );

        return $this;
    }

    /**
     * Change payment token column
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function changePaymentTokenColumn($installer)
    {
        $installer->getConnection()->changeColumn(
            $installer->getTable('aw_sarp2_profile'),
            'payment_token_id',
            'payment_token_id',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'Payment Token Id'
            ]
        );

        return $this;
    }
}
