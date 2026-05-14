<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\PayPalDetails;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Model\Payment\Token;
use Braintree\Transaction\PayPalDetails;

/**
 * Class ToToken
 * @package Aheadworks\Sarp2\PaymentData\Braintree\PayPalDetails
 */
class ToToken
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var ExpirationDate
     */
    private $expirationDate;

    /**
     * @param PaymentTokenInterfaceFactory $tokenFactory
     * @param ExpirationDate $expirationDate
     */
    public function __construct(
        PaymentTokenInterfaceFactory $tokenFactory,
        ExpirationDate $expirationDate
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->expirationDate = $expirationDate;
    }

    /**
     * Convert PayPal transaction detail into payment token
     *
     * @param PayPalDetails $payPalDetails
     * @return PaymentTokenInterface
     */
    public function convert($payPalDetails)
    {
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->tokenFactory->create();
        $paymentToken->setPaymentMethod('braintree_paypal')
            ->setType(Token::TOKEN_TYPE_ACCOUNT)
            ->setTokenValue($payPalDetails->token)
            ->setDetails('payerEmail', $payPalDetails->payerEmail)
            ->setExpiresAt($this->expirationDate->getFormatted());
        return $paymentToken;
    }
}
