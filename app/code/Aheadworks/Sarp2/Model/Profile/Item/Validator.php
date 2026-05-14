<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Profile\Item
 */
class Validator extends AbstractValidator
{
    /**
     * Returns true if and only if profile item entity meets the validation requirements
     *
     * @param ProfileItemInterface $item
     * @return bool
     */
    public function isValid($item)
    {
        $this->_clearMessages();

        // todo: M2SARP-383

        return empty($this->getMessages());
    }
}
