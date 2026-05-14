<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine;

/**
 * Interface EngineInterface
 * @package Aheadworks\Sarp2\Engine
 */
interface EngineInterface
{
    /**
     * Process payments specified by ids
     *
     * @param array $paymentIds
     * @return void
     */
    public function processPayments($paymentIds);

    /**
     * Process payments for today
     *
     * @return void
     */
    public function processPaymentsForToday();
}
