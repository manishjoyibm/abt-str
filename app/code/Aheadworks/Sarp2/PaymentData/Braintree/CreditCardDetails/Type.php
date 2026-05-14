<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails;

use Braintree\CreditCard;
use PayPal\Braintree\Gateway\Config\Config as BraintreeGatewayConfig;

/**
 * Class Type
 *
 * @package Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails
 */
class Type
{
    /**
     * @var BraintreeGatewayConfig
     */
    private $braintreeGatewayConfig;

    /**
     * @param BraintreeGatewayConfig $braintreeGatewayConfig
     */
    public function __construct(
        BraintreeGatewayConfig $braintreeGatewayConfig
    ) {
        $this->braintreeGatewayConfig = $braintreeGatewayConfig;
    }

    /**
     * Get prepared credit card type
     *
     * @param CreditCard $creditCard
     * @return string
     */
    public function getPrepared($creditCard)
    {
        $creditCardType = $creditCard->cardType;
        $replacedCreditCardType = str_replace(' ', '-', strtolower($creditCardType));
        $mapper = $this->braintreeGatewayConfig->getCctypesMapper();
        return isset($mapper[$replacedCreditCardType]) ? $mapper[$replacedCreditCardType] : '';
    }
}
