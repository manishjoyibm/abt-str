<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

use PayPal\Braintree\Model\Ui\ConfigProvider;

/**
 * Class Braintree
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
class Braintree extends AbstractCreditCardRenderer
{
    /**
     * {@inheritdoc}
     */
    public function canRender($token)
    {
        return $token->getPaymentMethod() === ConfigProvider::CODE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardNumber($tokenDetails)
    {
        return isset($tokenDetails['maskedCC']) ? $tokenDetails['maskedCC'] : '';
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
        return isset($tokenDetails['type']) ? $tokenDetails['type'] : '';
    }
}
