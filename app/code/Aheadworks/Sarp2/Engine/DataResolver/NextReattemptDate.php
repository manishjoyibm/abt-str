<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\DataResolver;

use Magento\Framework\Stdlib\DateTime;

/**
 * Class NextReattemptDate
 * @package Aheadworks\Sarp2\Engine\DataResolver
 */
class NextReattemptDate
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var array
     */
    private $reattemptsSchedule = [
        0 => 3,
        1 => 5
    ];

    /**
     * @param DateTime $dateTime
     */
    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Get next payment reattempt date using current payment date
     *
     * @param string $paymentDate
     * @param int $reattemptsCount
     * @return string
     */
    public function getDateNext($paymentDate, $reattemptsCount = 0)
    {
        $date = new \DateTime($paymentDate);
        if (isset($this->reattemptsSchedule[$reattemptsCount])) {
            $date->modify('+' . $this->reattemptsSchedule[$reattemptsCount] . ' day');
        }
        return $this->dateTime->formatDate($date);
    }

    /**
     * Get last payment reattempt date
     *
     * @param string $paymentDate
     * @param int $reattemptsCount
     * @return string
     */
    public function getLastDate($paymentDate, $reattemptsCount = 0)
    {
        $date = new \DateTime($paymentDate);
        if (isset($this->reattemptsSchedule[$reattemptsCount])) {
            $remainingDays = 0;
            for ($attempt = $reattemptsCount; $attempt < count($this->reattemptsSchedule); $attempt++) {
                $remainingDays += $this->reattemptsSchedule[$attempt];
            }
            $date->modify('+' . $remainingDays . ' day');
        }
        return $this->dateTime->formatDate($date);
    }
}
