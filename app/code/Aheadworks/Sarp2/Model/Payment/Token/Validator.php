<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Token;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Payment\Token
 */
class Validator extends AbstractValidator
{
    /**
     * Returns true if and only if payment token entity meets the validation requirements
     *
     * @param PaymentTokenInterface $token
     * @return bool
     */
    public function isValid($token)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($token->getPaymentMethod(), 'NotEmpty')) {
            $this->_addMessages(['Payment method is required.']);
        }
        if (!\Zend_Validate::is($token->getType(), 'NotEmpty')) {
            $this->_addMessages(['Token type is required.']);
        }
        if (!\Zend_Validate::is($token->getTokenValue(), 'NotEmpty')) {
            $this->_addMessages(['Token value is required.']);
        }

        return empty($this->getMessages());
    }
}
