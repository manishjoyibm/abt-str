<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;

/**
 * Interface PaymentDataObjectInterface
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data
 */
interface PaymentDataObjectInterface
{
    /**
     * Retrieve profile
     *
     * @return ProfileInterface
     */
    public function getProfile();

    /**
     * Retrieve payment
     *
     * @return SamplerInfoInterface
     */
    public function getPayment();
}
