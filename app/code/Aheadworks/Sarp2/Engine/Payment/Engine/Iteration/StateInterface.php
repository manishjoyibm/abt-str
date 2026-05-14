<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Engine\Iteration;

/**
 * Interface StateInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Iteration
 */
interface StateInterface
{
    /**
     * Get store Id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Get payment type
     *
     * @return string
     */
    public function getPaymentType();

    /**
     * Get timezone offset
     *
     * @return int
     */
    public function getTimezoneOffset();
}
