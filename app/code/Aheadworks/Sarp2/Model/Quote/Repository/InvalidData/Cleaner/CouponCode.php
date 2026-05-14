<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\CleanerInterface;

/**
 * Class CouponCode
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner
 */
class CouponCode implements CleanerInterface
{
    /**
     * {@inheritdoc}
     */
    public function clean($quote)
    {
        $quote->setCouponCode('');
        return $quote;
    }
}
