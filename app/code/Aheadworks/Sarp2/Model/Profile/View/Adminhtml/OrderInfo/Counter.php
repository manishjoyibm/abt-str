<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\View\Adminhtml\OrderInfo;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Class Counter
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Adminhtml\OrderInfo
 */
class Counter
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var PaymentsList
     */
    private $paymentList;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param PaymentsList $paymentList
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        PaymentsList $paymentList
    ) {
        $this->profileRepository = $profileRepository;
        $this->paymentList = $paymentList;
    }

    /**
     * Count left orders
     *
     * @param int $profileId
     * @return bool|int
     */
    public function countLeftOrders($profileId)
    {
        $ordersLeft = 0;
        try {
            /** @var ProfileInterface $profile */
            $profile = $this->profileRepository->get($profileId);
            /** @var ScheduleInterface $schedule */
            $schedule = $this->getSchedule($profileId);

            $planDefinition = $profile->getProfileDefinition();
            if ($planDefinition->getTotalBillingCycles() && $schedule) {
                $ordersLeft = $planDefinition->getTotalBillingCycles();
                if ($planDefinition->getTrialTotalBillingCycles()) {
                    $ordersLeft += $planDefinition->getTrialTotalBillingCycles();
                }
                $ordersLeft = $ordersLeft - $schedule->getTrialCount() - $schedule->getRegularCount();
            }
        } catch (\Exception $e) {
            return false;
        }

        return $ordersLeft;
    }

    /**
     * Get payment schedule for profile
     *
     * @param int $profileId
     * @return ScheduleInterface|bool
     */
    private function getSchedule($profileId)
    {
        $payments = $this->paymentList->getLastScheduled($profileId);
        if (count($payments)) {
            /** @var PaymentInterface $payment */
            $payment = current($payments);
            return $payment->getSchedule();
        } else {
            return false;
        }
    }
}
