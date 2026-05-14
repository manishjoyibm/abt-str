<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Grand;

/**
 * Class Summator
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Grand
 */
class Summator
{
    /**
     * @var array
     */
    private $totalAmounts = [];

    /**
     * Set total amount
     *
     * @param string $totalCode
     * @param float $amount
     * @return void
     */
    public function setTotalAmount($totalCode, $amount)
    {
        $this->totalAmounts[$totalCode] = $amount;
    }

    /**
     * Get totals sum
     *
     * @return float
     */
    public function getSum()
    {
        $result = 0;
        foreach ($this->totalAmounts as $code => $amount) {
            $result += $amount;
            unset($this->totalAmounts[$code]);
        }
        return $result;
    }
}
