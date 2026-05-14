<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Notification\DataResolver;

use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class ResolveSubject
 * @package Aheadworks\Sarp2\Engine\Notification\DataResolver
 */
class ResolveSubject
{
    /**
     * @var PaymentInterface
     */
    private $sourcePayment;

    /**
     * @var PaymentInterface[]
     */
    private $nextPayments;

    /**
     * @param $sourcePayment
     * @param array $nextPayments
     */
    public function __construct(
        $sourcePayment,
        array $nextPayments = []
    ) {
        $this->sourcePayment = $sourcePayment;
        $this->nextPayments = $nextPayments;
    }

    /**
     * Get source payment
     *
     * @return PaymentInterface
     */
    public function getSourcePayment()
    {
        return $this->sourcePayment;
    }

    /**
     * Get next scheduled payments
     *
     * @return PaymentInterface[]
     */
    public function getNextPayments()
    {
        return $this->nextPayments;
    }
}
