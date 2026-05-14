<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Model\ResourceModel\Engine\Schedule as ScheduleResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Schedule
 * @package Aheadworks\Sarp2\Engine\Payment
 */
class Schedule extends AbstractModel implements ScheduleInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ScheduleResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduleId()
    {
        return $this->getData('schedule_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setScheduleId($scheduleId)
    {
        return $this->setData('schedule_id', $scheduleId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPeriod()
    {
        return $this->getData('period');
    }

    /**
     * {@inheritdoc}
     */
    public function setPeriod($period)
    {
        return $this->setData('period', $period);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrequency()
    {
        return $this->getData('frequency');
    }

    /**
     * {@inheritdoc}
     */
    public function setFrequency($frequency)
    {
        return $this->setData('frequency', $frequency);
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialPaid()
    {
        return (bool) $this->getData('is_initial_paid');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInitialPaid($isInitialPaid)
    {
        return $this->setData('is_initial_paid', $isInitialPaid);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialCount()
    {
        return $this->getData('trial_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialCount($trialCount)
    {
        return $this->setData('trial_count', $trialCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTotalCount()
    {
        return $this->getData('trial_total_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTotalCount($trialTotalCount)
    {
        return $this->setData('trial_total_count', $trialTotalCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularCount()
    {
        return $this->getData('regular_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularCount($regularCount)
    {
        return $this->setData('regular_count', $regularCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularTotalCount()
    {
        return $this->getData('regular_total_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularTotalCount($regularTotalCount)
    {
        return $this->setData('regular_total_count', $regularTotalCount);
    }

    /**
     * {@inheritdoc}
     */
    public function isReactivated()
    {
        return (bool) $this->getData('is_reactivated');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsReactivated($isReactivated)
    {
        return $this->setData('is_reactivated', $isReactivated);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function isMembershipModel()
    {
        return $this->getData('is_membership_model');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsMembershipModel($isMembershipModel)
    {
        return $this->setData('is_membership_model', $isMembershipModel);
    }
}
