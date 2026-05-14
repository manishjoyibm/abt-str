<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Plan\Period;

use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments\Converter;

/**
 * Class Formatter
 * @package Aheadworks\Sarp2\Model\Plan\Period
 */
class Formatter
{
    /**
     * @var BillingPeriodSource
     */
    private $billingPeriodSource;

    /**
     * @var RepeatPaymentsSource
     */
    private $repeatPaymentsSource;

    /**
     * @var Converter
     */
    private $repeatPaymentsConverter;

    /**
     * @param BillingPeriodSource $billingPeriodSource
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param Converter $repeatPaymentsConverter
     */
    public function __construct(
        BillingPeriodSource $billingPeriodSource,
        RepeatPaymentsSource $repeatPaymentsSource,
        Converter $repeatPaymentsConverter
    ) {
        $this->billingPeriodSource = $billingPeriodSource;
        $this->repeatPaymentsSource = $repeatPaymentsSource;
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
    }

    /**
     * Format subscription plan period total cycles
     *
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @param int $totalCycles
     * @return string
     */
    public function formatTotalCycles($billingPeriod, $billingFrequency, $totalCycles)
    {
        $this->assertBillingPeriod($billingPeriod);

        $options = $this->billingPeriodSource->getOptions();
        $singlePeriodCycles = $billingFrequency * $totalCycles;
        return $singlePeriodCycles . ' ' .
            ($singlePeriodCycles == 1 ? $options[$billingPeriod] : $options[$billingPeriod] . 's');
    }

    /**
     * Format subscription plan period
     *
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @return string
     */
    public function format($billingPeriod, $billingFrequency)
    {
        $this->assertBillingPeriod($billingPeriod);

        $repeatPayment = $this->repeatPaymentsConverter->toRepeatPayments($billingPeriod, $billingFrequency);
        if ($repeatPayment) {
            $repeatPaymentOptions = $this->repeatPaymentsSource->getOptions();
            $periodFormatted = __('%1', $repeatPaymentOptions[$repeatPayment]);
        } else {
            $billingPeriodOptions = $this->billingPeriodSource->getOptions();
            $periodFormatted = __(
                'Every %1 %2',
                $billingFrequency,
                $billingFrequency > 1
                    ? $billingPeriodOptions[$billingPeriod] . 's'
                    : $billingPeriodOptions[$billingPeriod]
            );
        }

        return $periodFormatted->render();
    }

    /**
     * Asserts is a correct billing period
     *
     * @param string $billingPeriod
     * @return void
     */
    private function assertBillingPeriod($billingPeriod)
    {
        $options = $this->billingPeriodSource->getOptions();
        if (!isset($options[$billingPeriod])) {
            throw new \InvalidArgumentException('Invalid billing period parameter.');
        }
    }
}
