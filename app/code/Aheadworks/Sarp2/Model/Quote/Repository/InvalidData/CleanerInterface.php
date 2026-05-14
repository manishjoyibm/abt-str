<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData;

use Magento\Quote\Model\Quote;

/**
 * Interface CleanerInterface
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData
 */
interface CleanerInterface
{
    /**
     * Clean quote data
     *
     * @param Quote $quote
     * @return Quote
     */
    public function clean($quote);
}
