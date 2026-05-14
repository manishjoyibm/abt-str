<?php

namespace Abbott\Sarp2\Model\Payment\Token;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Laminas\Validator\StaticValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Payment\Token
 */
class Validator extends \Aheadworks\Sarp2\Model\Payment\Token\Validator
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

        if (!StaticValidator::execute($token->getPaymentMethod(), 'NotEmpty')) {
            $this->_addMessages(['Payment method is required.']);
        }
        if (!StaticValidator::execute($token->getType(), 'NotEmpty')) {
            $this->_addMessages(['Token type is required.']);
        }
        if (!StaticValidator::execute($token->getTokenValue(), 'NotEmpty')) {
            $this->_addMessages(['Token value is required.']);
        }

        return empty($this->getMessages());
    }
}