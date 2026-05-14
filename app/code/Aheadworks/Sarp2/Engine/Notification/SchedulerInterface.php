<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Notification;

use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Interface SchedulerInterface
 * @package Aheadworks\Sarp2\Engine\Notification
 */
interface SchedulerInterface
{
    /**
     * Schedule notification
     *
     * @param PaymentInterface $sourcePayment
     * @return NotificationInterface|null
     */
    public function schedule($sourcePayment);
}
