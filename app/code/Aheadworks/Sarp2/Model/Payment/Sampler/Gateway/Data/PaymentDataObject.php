<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;

/**
 * Class PaymentDataObject
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data
 */
class PaymentDataObject implements PaymentDataObjectInterface
{
    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var SamplerInfoInterface
     */
    private $payment;

    /**
     * @param ProfileInterface $profile
     * @param SamplerInfoInterface $payment
     */
    public function __construct(
        ProfileInterface $profile,
        SamplerInfoInterface $payment
    ) {
        $this->profile = $profile;
        $this->payment = $payment;
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
