<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Engine\Exception\PaymentException;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultInterface;

/**
 * Interface ActionInterface
 * @package Aheadworks\Sarp2\Engine\Payment
 */
interface ActionInterface
{
    /**
     * Payment action types
     */
    const TYPE_SINGLE = 'single';
    const TYPE_BUNDLED = 'bundled';

    /**
     * Perform pay action
     *
     * @param PaymentInterface $payment
     * @return ResultInterface
     * @throws PaymentException
     */
    public function pay(PaymentInterface $payment);
}
