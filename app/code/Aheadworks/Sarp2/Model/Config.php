<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\Sarp2\Model
 */
class Config
{
    /**
     * Configuration path to default shipping method
     */
    const XML_PATH_DEFAULT_SHIPPING_METHOD = 'aw_sarp2/general/default_shipping_method';

    /**
     * Configuration path to log enabled flag
     */
    const XML_PATH_LOG_ENABLED = 'aw_sarp2/general/log_enabled';

    /**
     * Configuration path to email identity
     */
    const XML_PATH_EMAIL_IDENTITY = 'aw_sarp2/email_settings/email_identity';

    /**
     * Configuration path to upcoming billing email offset
     */
    const XML_PATH_UPCOMING_BILLING_EMAIL_OFFSET = 'aw_sarp2/email_settings/upcoming_billing_email_days_offset';

    /**
     * Configuration path subscription editing - can switch to another plan
     */
    const XML_PATH_CAN_SWITCH_TO_ANOTHER_PLAN = 'aw_sarp2/subscription_editing/can_switch_to_another_plan';

    /**
     * Configuration path subscription editing - can change subscription address
     */
    const XML_PATH_CAN_CHANGE_SUBSCRIPTION_ADDRESS = 'aw_sarp2/subscription_editing/can_change_shipping_address';

    /**
     * Configuration path subscription editing - can edit next payment date
     */
    const XML_PATH_CAN_EDIT_NEXT_PAYMENT_DATE = 'aw_sarp2/subscription_editing/can_edit_next_payment_date';

    /**
     * Configuration path subscription editing - earliest next payment date
     */
    const XML_PATH_EARLIEST_NEXT_PAYMENT_DATE = 'aw_sarp2/subscription_editing/earliest_next_payment_date';

    /**
     * Configuration path subscription editing - can edit next payment date for membership
     */
    const XML_PATH_CAN_EDIT_NEXT_PAYMENT_DATE_FOR_MEMBERSHIP
        = 'aw_sarp2/subscription_editing/can_edit_next_payment_date_for_membership';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get default shipping method
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDefaultShippingMethod($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_SHIPPING_METHOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if logging enabled
     *
     * @return bool
     */
    public function isLogEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LOG_ENABLED);
    }

    /**
     * Get email identity
     *
     * @param int $storeId
     * @return string
     */
    public function getEmailIdentity($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get sender data
     *
     * @param int $storeId
     * @return array
     */
    public function getSenderData($storeId)
    {
        $emailIdentity = $this->getEmailIdentity($storeId);
        return $this->scopeConfig->getValue(
            'trans_email/ident_' . $emailIdentity,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get upcoming billing email offset
     *
     * @param int $storeId
     * @return int
     */
    public function getUpcomingBillingEmailOffset($storeId)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_UPCOMING_BILLING_EMAIL_OFFSET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Can switch to another plan
     *
     * @return int
     */
    public function canSwitchToAnotherPlan()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_SWITCH_TO_ANOTHER_PLAN,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Can change subscription address
     *
     * @return int
     */
    public function canChangeSubscriptionAddress()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_CHANGE_SUBSCRIPTION_ADDRESS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Can edit next payment date
     *
     * @param int|null $websiteId
     * @return int
     */
    public function canEditNextPaymentDate($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_EDIT_NEXT_PAYMENT_DATE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Can edit next payment date
     *
     * @param int|null $storeId
     * @return int
     */
    public function getEarliestNextPaymentDate($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_EARLIEST_NEXT_PAYMENT_DATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Can edit next payment date for membership
     *
     * @param int|null $websiteId
     * @return int
     */
    public function canEditNextPaymentDateForMembership($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_EDIT_NEXT_PAYMENT_DATE_FOR_MEMBERSHIP,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
