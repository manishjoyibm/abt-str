<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\DataResolver;

use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;

/**
 * Class NextPaymentDate
 * @package Aheadworks\Sarp2\Engine\DataResolver
 */
class NextPaymentDate
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @param DateTime $dateTime
     * @param CoreDate $coreDate
     */
    public function __construct(
        DateTime $dateTime,
        CoreDate $coreDate
    ) {
        $this->dateTime = $dateTime;
        $this->coreDate = $coreDate;
    }

    /**
     * Get initial value of next payment date
     *
     * @param string $startDate
     * @return string
     */
    public function getDateInitial($startDate)
    {
        $timestamp = max(
            $this->coreDate->gmtTimestamp($startDate),
            $this->coreDate->gmtTimestamp()
        );
        return $this->dateTime->formatDate($timestamp);
    }

    /**
     * Get next payment date using current payment date
     *
     * @param string $paymentDate
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @return string
     */
    public function getDateNext($paymentDate, $billingPeriod, $billingFrequency)
    {
        $date = new \DateTime($paymentDate);
        switch ($billingPeriod) {
            case BillingPeriod::DAY:
                $date->modify('+' . $billingFrequency . ' day');
                break;
            case BillingPeriod::WEEK:
                $date->modify('+' . $billingFrequency . ' week');
                break;
            case BillingPeriod::SEMI_MONTH:
                $date->modify('+' . $billingFrequency * 2 . ' week');
                break;
            case BillingPeriod::MONTH:
                $date->modify('+' . $billingFrequency . ' month');
                break;
            case BillingPeriod::YEAR:
                $date->modify('+' . $billingFrequency . ' year');
                break;
            default:
                break;
        }
        return $this->dateTime->formatDate($date);
    }

    /**
     * Get next payment date of outstanding payment
     *
     * @param string $paymentDate
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @return string
     */
    public function getDateNextForOutstanding($paymentDate, $billingPeriod, $billingFrequency)
    {
        $result = $paymentDate;
        $today = $this->dateTime->formatDate(true);
        while ($result < $today) {
            $result = $this->getDateNext($result, $billingPeriod, $billingFrequency);
        }
        return $result;
    }
}
