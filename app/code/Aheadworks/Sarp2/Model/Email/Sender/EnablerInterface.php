<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Email\Sender;

use Aheadworks\Sarp2\Engine\NotificationInterface;

/**
 * Interface EnablerInterface
 * @package Aheadworks\Sarp2\Model\Email\Sender
 */
interface EnablerInterface
{
    /**
     * Check if sender enabled
     *
     * @param NotificationInterface $notification
     * @return bool
     */
    public function isEnabled($notification);
}
