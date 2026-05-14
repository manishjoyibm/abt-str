<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Adapter\StripePayments;

use Magento\Framework\DataObject;

/**
 * Class Response
 * @package Aheadworks\Sarp2\Model\Adapter\StripePayments
 */
class Response extends DataObject
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID        = 'id';
    const STATUS    = 'status';
    /**#@-*/

    /**
     * Get transaction id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
}
