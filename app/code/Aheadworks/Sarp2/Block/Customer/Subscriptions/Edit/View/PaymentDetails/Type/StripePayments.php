<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

/**
 * Class StripePayments
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
class StripePayments extends AbstractCreditCardRenderer
{
    /**
     * Payment method code
     */
    const PAYMENT_METHOD_CODE = 'stripe_payments';

    /**
     * {@inheritdoc}
     */
    public function canRender($token)
    {
        return $token->getPaymentMethod() === self::PAYMENT_METHOD_CODE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardNumber($tokenDetails)
    {
        return isset($tokenDetails['lastCcNumber']) ? $tokenDetails['lastCcNumber'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate($tokenDetails)
    {
        return isset($tokenDetails['expirationDate']) ? $tokenDetails['expirationDate'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardType($tokenDetails)
    {
        return isset($tokenDetails['typeCode']) ? $tokenDetails['typeCode'] : '';
    }
}
