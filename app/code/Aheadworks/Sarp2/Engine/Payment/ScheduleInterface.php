<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment;

/**
 * Interface ScheduleInterface
 * @package Aheadworks\Sarp2\Engine\Payment
 */
interface ScheduleInterface
{
    /**
     * Get schedule Id
     *
     * @return int|null
     */
    public function getScheduleId();

    /**
     * Set schedule Id
     *
     * @param int $scheduleId
     * @return $this
     */
    public function setScheduleId($scheduleId);

    /**
     * Get payments period
     *
     * @return string
     */
    public function getPeriod();

    /**
     * Set payments period
     *
     * @param string $period
     * @return $this
     */
    public function setPeriod($period);

    /**
     * Get payments frequency
     *
     * @return int
     */
    public function getFrequency();

    /**
     * Get payments frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency);

    /**
     * Check if initial fee paid
     *
     * @return bool
     */
    public function isInitialPaid();

    /**
     * Set initial fee paid flag
     *
     * @param bool $isInitialPaid
     * @return $this
     */
    public function setIsInitialPaid($isInitialPaid);

    /**
     * Get trial payments count
     *
     * @return int
     */
    public function getTrialCount();

    /**
     * Set trial payments count
     *
     * @param int $trialCount
     * @return $this
     */
    public function setTrialCount($trialCount);

    /**
     * Get trial payments total count
     *
     * @return int
     */
    public function getTrialTotalCount();

    /**
     * Set trial payments total count
     *
     * @param int $trialTotalCount
     * @return $this
     */
    public function setTrialTotalCount($trialTotalCount);

    /**
     * Get regular payments count
     *
     * @return int
     */
    public function getRegularCount();

    /**
     * Set regular payments count
     *
     * @param int $regularCount
     * @return $this
     */
    public function setRegularCount($regularCount);

    /**
     * Get regular payments total count
     *
     * @return int
     */
    public function getRegularTotalCount();

    /**
     * Set regular payments total count
     *
     * @param int $regularTotalCount
     * @return $this
     */
    public function setRegularTotalCount($regularTotalCount);

    /**
     * Check if schedule is reactivated
     *
     * @return bool
     */
    public function isReactivated();

    /**
     * Set reactivated flag
     *
     * @param bool $isReactivated
     * @return $this
     */
    public function setIsReactivated($isReactivated);

    /**
     * Get store Id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get is membership model
     *
     * @return bool
     */
    public function isMembershipModel();

    /**
     * Set is membership model
     *
     * @param bool $isMembershipModel
     * @return $this
     */
    public function setIsMembershipModel($isMembershipModel);
}
