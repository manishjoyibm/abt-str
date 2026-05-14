<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\StripePayments\PaymentIntent;

use Aheadworks\Sarp2\Gateway\StripePayments\Config\Config as StripeConfig;

/**
 * Class CreditCardType
 * @package Aheadworks\Sarp2\PaymentData\StripePayments\PaymentIntent
 */
class CreditCardType
{
    /**
     * @var StripeConfig
     */
    private $stripeConfig;

    /**
     * @param StripeConfig $stripeConfig
     */
    public function __construct(
        StripeConfig $stripeConfig
    ) {
        $this->stripeConfig = $stripeConfig;
    }

    /**
     * Get prepared credit card type
     *
     * @param string $typeCode
     * @return string
     */
    public function getPrepared($typeCode)
    {
        $map = $this->stripeConfig->getCcTypesMap();
        return isset($map[$typeCode]) ? $map[$typeCode] : '';
    }
}
