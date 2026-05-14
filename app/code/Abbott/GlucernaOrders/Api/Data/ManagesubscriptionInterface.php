<?php

namespace Abbott\GlucernaOrders\Api\Data;

interface ManagesubscriptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const IS_DEFAULT_PLAN = 'is_default_plan';
    const MANAGESUBSCRIPTION_ID = 'managesubscription_id';
    const IS_ACTIVE_PLAN = 'is_active_plan';
    const PLAN_CODE = 'plan_code';
    const PLAN_VALUE = 'plan_value';
    const PLAN_SHIPPING_RATE = 'plan_shipping_rate';
    const TRIAL_PERIOD = 'trial_period';
    const PLAN_LABEL = 'plan_label';
    const PLAN_NAME = 'plan_name';
    const PLAN_PRICE = 'plan_price';
    const IS_TRIAL_PLAN = 'is_trial_plan';

    /**
     * Get managesubscription_id
     * @return string|null
     */
    public function getManagesubscriptionId();

    /**
     * Set managesubscription_id
     * @param string $managesubscriptionId
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setManagesubscriptionId($managesubscriptionId);

    /**
     * Get plan_code
     * @return string|null
     */
    public function getPlanCode();

    /**
     * Set plan_code
     * @param string $planCode
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanCode($planCode);

    /**
     * Get plan_name
     * @return string|null
     */
    public function getPlanName();

    /**
     * Set plan_name
     * @param string $planName
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanName($planName);

    /**
     * Get plan_label
     * @return string|null
     */
    public function getPlanLabel();

    /**
     * Set plan_label
     * @param string $planLabel
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanLabel($planLabel);

    /**
     * Get trial_period
     * @return string|null
     */
    public function getTrialPeriod();

    /**
     * Set trial_period
     * @param string $trialPeriod
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setTrialPeriod($trialPeriod);

    /**
     * Get plan_value
     * @return string|null
     */
    public function getPlanValue();

    /**
     * Set plan_value
     * @param string $planValue
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanValue($planValue);

    /**
     * Get plan_price
     * @return string|null
     */
    public function getPlanPrice();

    /**
     * Set plan_price
     * @param string $planPrice
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanPrice($planPrice);

    /**
     * Get plan_shipping_rate
     * @return string|null
     */
    public function getPlanShippingRate();

    /**
     * Set plan_shipping_rate
     * @param string $planShippingRate
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanShippingRate($planShippingRate);

    /**
     * Get is_trial_plan
     * @return string|null
     */
    public function getIsTrialPlan();

    /**
     * Set is_trial_plan
     * @param string $isTrialPlan
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setIsTrialPlan($isTrialPlan);

    /**
     * Get is_default_plan
     * @return string|null
     */
    public function getIsDefaultPlan();

    /**
     * Set is_default_plan
     * @param string $isDefaultPlan
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setIsDefaultPlan($isDefaultPlan);

    /**
     * Get is_active_plan
     * @return string|null
     */
    public function getIsActivePlan();

    /**
     * Set is_active_plan
     * @param string $isActivePlan
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setIsActivePlan($isActivePlan);
}
