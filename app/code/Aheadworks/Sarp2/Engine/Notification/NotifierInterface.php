<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Notification;

/**
 * Interface NotifierInterface
 * @package Aheadworks\Sarp2\Engine\Notification
 */
interface NotifierInterface
{
    /**
     * Process notifications for today
     *
     * @return void
     */
    public function processNotificationsForToday();
}
