<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\Address;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Profile\Address
 */
class Validator extends AbstractValidator
{
    /**
     * Returns true if and only if profile address entity meets the validation requirements
     *
     * @param ProfileAddressInterface $address
     * @return bool
     */
    public function isValid($address)
    {
        $this->_clearMessages();

        // todo: M2SARP-383

        return empty($this->getMessages());
    }
}
