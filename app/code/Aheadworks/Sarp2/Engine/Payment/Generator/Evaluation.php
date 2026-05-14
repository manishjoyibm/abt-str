<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Generator;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetails;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetailsFactory;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;

/**
 * Class Evaluation
 * @package Aheadworks\Sarp2\Engine\Payment\Generator
 */
class Evaluation
{
    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var PaymentDetailsFactory
     */
    private $detailsFactory;

    /**
     * @param CoreDate $coreDate
     * @param NextPaymentDate $nextPaymentDate
     * @param PaymentDetailsFactory $detailsFactory
     */
    public function __construct(
        CoreDate $coreDate,
        NextPaymentDate $nextPaymentDate,
        PaymentDetailsFactory $detailsFactory
    ) {
        $this->coreDate = $coreDate;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->detailsFactory = $detailsFactory;
    }

    /**
     * Evaluate possible payment details for current date.
     * Assumed that current date is a payment date candidate.
     * Returns an empty array if there is no possible payments
     *
     * @param ScheduleInterface $schedule
     * @param ProfileInterface $profile
     * @param string $currentDate
     * @param string|null $lastPaymentDate
     * @return PaymentDetails[]
     */
    public function evaluate(
        ScheduleInterface $schedule,
        ProfileInterface $profile,
        $currentDate,
        $lastPaymentDate = null
    ) {
        $result = [];

        $wasPayments = $lastPaymentDate !== null;
        $baseDate = $wasPayments
            ? $lastPaymentDate
            : $profile->getStartDate();

        $baseTm = $this->getGmtTimestampExclTime($baseDate);
        $currentTm = $this->getGmtTimestampExclTime($currentDate);

        $estimateTypes = $wasPayments
            ? $currentTm >= $this->getGmtTimestampExclTime(
                $this->nextPaymentDate->getDateNext(
                    $lastPaymentDate,
                    $schedule->getPeriod(),
                    $schedule->getFrequency()
                )
            )
            : true;

        if ($estimateTypes) {
            if ($profile->getProfileDefinition()->getIsInitialFeeEnabled() && !$schedule->isInitialPaid()) {
                $result[] = $this->detailsFactory->create(
                    [
                        'paymentPeriod' => PaymentInterface::PERIOD_INITIAL,
                        'paymentType' => PaymentInterface::TYPE_PLANNED,
                        'date' => $profile->getStartDate(),
                        'totalAmount' => $profile->getInitialGrandTotal(),
                        'baseTotalAmount' => $profile->getBaseInitialGrandTotal()
                    ]
                );
            }
            if ($baseTm <= $currentTm) {
                $totalTrialCounts = $schedule->getTrialTotalCount();
                if ($totalTrialCounts > 0 && $schedule->getTrialCount() < $totalTrialCounts) {
                    $result[] = $this->detailsFactory->create(
                        [
                            'paymentPeriod' => PaymentInterface::PERIOD_TRIAL,
                            'paymentType' => PaymentInterface::TYPE_PLANNED,
                            'date' => $currentDate,
                            'totalAmount' => $profile->getTrialGrandTotal(),
                            'baseTotalAmount' => $profile->getBaseTrialGrandTotal()
                        ]
                    );
                } else {
                    $totalRegularCounts = $schedule->getRegularTotalCount();
                    if ($schedule->isMembershipModel() && $totalRegularCounts
                        && ($schedule->getRegularCount() + 1) == $totalRegularCounts
                    ) {
                        $result[] = $this->detailsFactory->create(
                            [
                                'paymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                                'paymentType' => PaymentInterface::TYPE_LAST_PERIOD_HOLDER,
                                'date' => $currentDate,
                                'totalAmount' => 0,
                                'baseTotalAmount' => 0
                            ]
                        );
                    } elseif ($totalRegularCounts == 0 || $schedule->getRegularCount() < $totalRegularCounts) {
                        $result[] = $this->detailsFactory->create(
                            [
                                'paymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                                'paymentType' => PaymentInterface::TYPE_PLANNED,
                                'date' => $currentDate,
                                'totalAmount' => $profile->getRegularGrandTotal(),
                                'baseTotalAmount' => $profile->getBaseRegularGrandTotal()
                            ]
                        );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get GMT timestamp without time
     *
     * @param string $date
     * @return int
     */
    private function getGmtTimestampExclTime($date)
    {
        $dateTime = (new \DateTime($date))
            ->setTime(0, 0, 0);
        return $this->coreDate->gmtTimestamp($dateTime);
    }
}
