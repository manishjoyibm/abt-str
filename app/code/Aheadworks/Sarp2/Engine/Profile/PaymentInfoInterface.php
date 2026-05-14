<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Interface PaymentInfoInterface
 * @package Aheadworks\Sarp2\Engine\Profile
 */
interface PaymentInfoInterface
{
    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile();

    /**
     * Get payment period
     *
     * @return string
     */
    public function getPaymentPeriod();
}
