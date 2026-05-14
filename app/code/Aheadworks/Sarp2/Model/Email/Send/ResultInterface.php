<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Email\Send;

/**
 * Interface ResultInterface
 * @package Aheadworks\Sarp2\Model\Email\Send
 */
interface ResultInterface
{
    /**
     * Check if email sent successfully
     *
     * @return bool
     */
    public function isSuccessful();

    /**
     * Check if email notification type is disabled
     *
     * @return bool
     */
    public function isDisabled();
}
