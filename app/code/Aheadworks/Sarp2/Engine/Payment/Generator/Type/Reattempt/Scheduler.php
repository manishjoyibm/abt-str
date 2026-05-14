<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\DataResolver\NextReattemptDate;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;

/**
 * Class Scheduler
 * @package Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt
 */
class Scheduler
{
    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var NextReattemptDate
     */
    private $nextReattemptDate;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var ScheduleResultFactory
     */
    private $resultFactory;

    /**
     * @param NextPaymentDate $nextPaymentDate
     * @param NextReattemptDate $nextReattemptDate
     * @param DateTime $dateTime
     * @param CoreDate $coreDate
     * @param ScheduleResultFactory $resultFactory
     */
    public function __construct(
        NextPaymentDate $nextPaymentDate,
        NextReattemptDate $nextReattemptDate,
        DateTime $dateTime,
        CoreDate $coreDate,
        ScheduleResultFactory $resultFactory
    ) {
        $this->nextPaymentDate = $nextPaymentDate;
        $this->nextReattemptDate = $nextReattemptDate;
        $this->dateTime = $dateTime;
        $this->coreDate = $coreDate;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Perform scheduling of payment reattempt date
     *
     * @param PaymentInterface $payment
     * @return ScheduleResult
     */
    public function schedule($payment)
    {
        $schedule = $payment->getSchedule();
        $today = $this->dateTime->formatDate(true);

        $nextPaymentDate = $this->nextPaymentDate->getDateNext(
            $payment->getScheduledAt(),
            $schedule->getPeriod(),
            $schedule->getFrequency()
        );
        $lastRetryDate = $this->nextReattemptDate->getLastDate($today);
        $nextPaymentDateTm = $this->coreDate->gmtTimestamp($nextPaymentDate);
        $lastRetryDateTm = $this->coreDate->gmtTimestamp($lastRetryDate);

        return $lastRetryDateTm > $nextPaymentDateTm || $payment->isBundled()
            ? $this->resultFactory->create(
                [
                    'type' => ScheduleResult::REATTEMPT_TYPE_NEXT,
                    'date' => $nextPaymentDate
                ]
            )
            : $this->resultFactory->create(
                [
                    'type' => ScheduleResult::REATTEMPT_TYPE_RETRY,
                    'date' => $this->nextReattemptDate->getDateNext($today)
                ]
            );
    }
}
