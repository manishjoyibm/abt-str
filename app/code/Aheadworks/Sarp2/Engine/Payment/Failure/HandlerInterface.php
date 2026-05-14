<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Failure;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\DataObject;

/**
 * Interface HandlerInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Failure
 */
interface HandlerInterface
{
    /**
     * Failure handler types
     */
    const TYPE_SINGLE = 'single';
    const TYPE_BUNDLE = 'bundle';

    /**
     * Maximum payment failures count
     */
    const MAX_PAYMENT_FAILURES = 3;

    /**
     * Handle payment exception
     *
     * @param PaymentInterface $payment
     * @param DataObject|null $failureInfo
     * @return PaymentInterface
     */
    public function handle($payment, $failureInfo = null);

    /**
     * Handle payment reattempt exception
     *
     * @param PaymentInterface $payment
     * @param DataObject|null $failureInfo
     * @return PaymentInterface
     */
    public function handleReattempt($payment, $failureInfo = null);
}
