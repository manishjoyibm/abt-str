<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Info;

/**
 * Class Amount
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Info
 */
class Amount
{
    /**
     * @var float|int
     */
    private $amount;

    /**
     * @param float|int $amount
     */
    public function __construct(
        $amount = 1
    ) {
        $this->amount = $amount;
    }

    /**
     * Get amount
     *
     * @return float|int
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
