<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Adapter\StripePayments;

use Magento\Framework\DataObject;

/**
 * Class CreditCard
 * @package Aheadworks\Sarp2\Model\Adapter\StripePayments
 */
class CreditCard extends DataObject
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const TYPE          = 'type';
    const LAST4         = 'last4';
    const EXP_MONTH     = 'exp_month';
    const EXP_YEAR      = 'exp_year';
    /**#@-*/

    /**
     * Get card type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Get last 4 numbers
     *
     * @return string
     */
    public function getLast4()
    {
        return $this->getData(self::LAST4);
    }

    /**
     * Get expiration date month
     *
     * @return string
     */
    public function getExpMonth()
    {
        return $this->getData(self::EXP_MONTH);
    }

    /**
     * Get expiration date year
     *
     * @return string
     */
    public function getExpYear()
    {
        return $this->getData(self::EXP_YEAR);
    }
}
