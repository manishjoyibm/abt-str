<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\GeneratorInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Engine\Payment\ScheduleFactory;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Initial
 * @package Aheadworks\Sarp2\Engine\Payment\Generator\Type
 */
class Initial implements GeneratorInterface
{
    /**
     * @var Evaluation
     */
    private $evaluation;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Evaluation $evaluation
     * @param NextPaymentDate $nextPaymentDate
     * @param PaymentFactory $paymentFactory
     * @param ScheduleFactory $scheduleFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        Evaluation $evaluation,
        NextPaymentDate $nextPaymentDate,
        PaymentFactory $paymentFactory,
        ScheduleFactory $scheduleFactory,
        DateTime $dateTime
    ) {
        $this->evaluation = $evaluation;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->paymentFactory = $paymentFactory;
        $this->scheduleFactory = $scheduleFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SourceInterface $source)
    {
        $payments = [];
        $profile = $source->getProfile();
        if ($profile) {
            /** @var Schedule $schedule */
            $schedule = $this->scheduleFactory->create();
            $planDefinition = $profile->getProfileDefinition();
            $regularTotalCount =
                $planDefinition->getIsMembershipModelEnabled() && $planDefinition->getTotalBillingCycles()
                    ? $planDefinition->getTotalBillingCycles() + 1
                    : $planDefinition->getTotalBillingCycles();
            $schedule->setPeriod($planDefinition->getBillingPeriod())
                ->setFrequency($planDefinition->getBillingFrequency())
                ->setTrialTotalCount(
                    $planDefinition->getIsTrialPeriodEnabled()
                        ? $planDefinition->getTrialTotalBillingCycles()
                        : 0
                )
                ->setRegularTotalCount($regularTotalCount)
                ->setStoreId($profile->getStoreId())
                ->setIsMembershipModel(
                    $planDefinition->getIsMembershipModelEnabled()
                        ? $planDefinition->getIsMembershipModelEnabled()
                        : false
                );

            $prePaymentInfo = $profile->getPrePaymentInfo();
            $isInitialPaid = $prePaymentInfo
                ? $prePaymentInfo->getIsInitialFeePaid()
                : false;
            $isTrialPaid = $prePaymentInfo
                ? $prePaymentInfo->getIsTrialPaid()
                : false;
            $isRegularPaid = $prePaymentInfo
                ? $prePaymentInfo->getIsRegularPaid()
                : false;

            $today = $this->dateTime->formatDate(true);
            $wasPrePayments = $isInitialPaid || $isTrialPaid || $isRegularPaid;
            if ($wasPrePayments) {
                $schedule->setIsInitialPaid($isInitialPaid)
                    ->setTrialCount($isTrialPaid ? 1 : 0)
                    ->setRegularCount($isRegularPaid ? 1 : 0);
                $nextPaymentDate = $this->nextPaymentDate->getDateNext(
                    $today,
                    $planDefinition->getBillingPeriod(),
                    $planDefinition->getBillingFrequency()
                );
                $paymentsDetails = $this->evaluation->evaluate(
                    $schedule,
                    $profile,
                    $nextPaymentDate,
                    $today
                );
            } else {
                $paymentsDetails = $this->evaluation->evaluate(
                    $schedule,
                    $profile,
                    $today
                );
            }

            foreach ($paymentsDetails as $details) {
                /** @var Payment $payment */
                $payment = $this->paymentFactory->create();
                $payment->setProfileId($profile->getProfileId())
                    ->setProfile($profile)
                    ->setType($details->getPaymentType())
                    ->setPaymentPeriod($details->getPaymentPeriod())
                    ->setPaymentStatus(PaymentInterface::STATUS_PLANNED)
                    ->setScheduledAt($details->getDate())
                    ->setPaymentData(['token_id' => $profile->getPaymentTokenId()])
                    ->setTotalScheduled($details->getTotalAmount())
                    ->setBaseTotalScheduled($details->getBaseTotalAmount())
                    ->setSchedule($schedule);
                $payments[] = $payment;
            }
        }
        return $payments;
    }
}
