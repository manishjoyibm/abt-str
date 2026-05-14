<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Interface CollectorInterface
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile
 */
interface CollectorInterface
{
    /**
     * Collect profile totals
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function collect(ProfileInterface $profile);
}
