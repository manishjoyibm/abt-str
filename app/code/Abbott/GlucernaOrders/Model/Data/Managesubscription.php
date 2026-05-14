<?php

namespace Abbott\GlucernaOrders\Model\Data;

use Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface;

class Managesubscription extends \Magento\Framework\Api\AbstractExtensibleObject implements ManagesubscriptionInterface
{

    /**
     * Get managesubscription_id
     * @return string|null
     */
    public function getManagesubscriptionId()
    {
        return $this->_get(self::MANAGESUBSCRIPTION_ID);
    }

    /**
     * Set managesubscription_id
     * @param string $managesubscriptionId
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setManagesubscriptionId($managesubscriptionId)
    {
        return $this->setData(self::MANAGESUBSCRIPTION_ID, $managesubscriptionId);
    }

    /**
     * Get plan_code
     * @return string|null
     */
    public function getPlanCode()
    {
        return $this->_get(self::PLAN_CODE);
    }

    /**
     * Set plan_code
     * @param string $planCode
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanCode($planCode)
    {
        return $this->setData(self::PLAN_CODE, $planCode);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get plan_name
     * @return string|null
     */
    public function getPlanName()
    {
        return $this->_get(self::PLAN_NAME);
    }

    /**
     * Set plan_name
     * @param string $planName
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanName($planName)
    {
        return $this->setData(self::PLAN_NAME, $planName);
    }

    /**
     * Get plan_label
     * @return string|null
     */
    public function getPlanLabel()
    {
        return $this->_get(self::PLAN_LABEL);
    }

    /**
     * Set plan_label
     * @param string $planLabel
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanLabel($planLabel)
    {
        return $this->setData(self::PLAN_LABEL, $planLabel);
    }

    /**
     * Get trial_period
     * @return string|null
     */
    public function getTrialPeriod()
    {
        return $this->_get(self::TRIAL_PERIOD);
    }

    /**
     * Set trial_period
     * @param string $trialPeriod
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setTrialPeriod($trialPeriod)
    {
        return $this->setData(self::TRIAL_PERIOD, $trialPeriod);
    }

    /**
     * Get plan_value
     * @return string|null
     */
    public function getPlanValue()
    {
        return $this->_get(self::PLAN_VALUE);
    }

    /**
     * Set plan_value
     * @param string $planValue
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanValue($planValue)
    {
        return $this->setData(self::PLAN_VALUE, $planValue);
    }

    /**
     * Get plan_price
     * @return string|null
     */
    public function getPlanPrice()
    {
        return $this->_get(self::PLAN_PRICE);
    }

    /**
     * Set plan_price
     * @param string $planPrice
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanPrice($planPrice)
    {
        return $this->setData(self::PLAN_PRICE, $planPrice);
    }

    /**
     * Get plan_shipping_rate
     * @return string|null
     */
    public function getPlanShippingRate()
    {
        return $this->_get(self::PLAN_SHIPPING_RATE);
    }

    /**
     * Set plan_shipping_rate
     * @param string $planShippingRate
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setPlanShippingRate($planShippingRate)
    {
        return $this->setData(self::PLAN_SHIPPING_RATE, $planShippingRate);
    }

    /**
     * Get is_trial_plan
     * @return string|null
     */
    public function getIsTrialPlan()
    {
        return $this->_get(self::IS_TRIAL_PLAN);
    }

    /**
     * Set is_trial_plan
     * @param string $isTrialPlan
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setIsTrialPlan($isTrialPlan)
    {
        return $this->setData(self::IS_TRIAL_PLAN, $isTrialPlan);
    }

    /**
     * Get is_default_plan
     * @return string|null
     */
    public function getIsDefaultPlan()
    {
        return $this->_get(self::IS_DEFAULT_PLAN);
    }

    /**
     * Set is_default_plan
     * @param string $isDefaultPlan
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setIsDefaultPlan($isDefaultPlan)
    {
        return $this->setData(self::IS_DEFAULT_PLAN, $isDefaultPlan);
    }

    /**
     * Get is_active_plan
     * @return string|null
     */
    public function getIsActivePlan()
    {
        return $this->_get(self::IS_ACTIVE_PLAN);
    }

    /**
     * Set is_active_plan
     * @param string $isActivePlan
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     */
    public function setIsActivePlan($isActivePlan)
    {
        return $this->setData(self::IS_ACTIVE_PLAN, $isActivePlan);
    }
}
