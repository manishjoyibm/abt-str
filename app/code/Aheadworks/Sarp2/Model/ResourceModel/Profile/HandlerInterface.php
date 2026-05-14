<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Interface HandlerInterface
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
interface HandlerInterface
{
    /**
     * Process profile saving/deletion
     *
     * @param ProfileInterface $profile
     * @return void
     */
    public function process(ProfileInterface $profile);
}
