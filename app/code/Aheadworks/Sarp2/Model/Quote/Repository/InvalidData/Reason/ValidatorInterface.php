<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason;

use Magento\Quote\Model\Quote;

/**
 * Interface ValidatorInterface
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason
 */
interface ValidatorInterface
{
    /**
     * Validate quote
     *
     * @param Quote $quote
     * @return bool
     */
    public function validate($quote);

    /**
     * Get invalid data reason
     *
     * @return int
     */
    public function getReason();

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage();
}
