<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

use Aheadworks\Sarp2\PaymentData\AuthorizenetAcceptjs\ProfileDetails\ToToken;

/**
 * Class AuthorizenetAcceptjs
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
class AuthorizenetAcceptjs extends AbstractCreditCardRenderer
{
    /**
     * {@inheritdoc}
     */
    public function canRender($token)
    {
        return $token->getPaymentMethod() === ToToken::METHOD;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardNumber($tokenDetails)
    {
        return isset($tokenDetails['ccLast4']) ? $tokenDetails['ccLast4'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate($tokenDetails)
    {
        return 'XXXX';
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardType($tokenDetails)
    {
        return isset($tokenDetails['accountType']) ? $tokenDetails['accountType'] : '';
    }
}
