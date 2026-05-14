<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\StripePayments\PaymentResponse;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\CreditCard;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\CreditCardResolver;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response as StripePaymentResponse;
use Aheadworks\Sarp2\PaymentData\StripePayments\ExpirationDate;

/**
 * Class ToToken
 *
 * @package Aheadworks\Sarp2\PaymentData\StripePayments\PaymentResponse
 */
class ToToken
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var CreditCardResolver
     */
    private $creditCardResolver;

    /**
     * @var ExpirationDate
     */
    private $expirationDate;

    /**
     * @param PaymentTokenInterfaceFactory $tokenFactory
     * @param CreditCardResolver $creditCardResolver
     * @param ExpirationDate $expirationDate
     */
    public function __construct(
        PaymentTokenInterfaceFactory $tokenFactory,
        CreditCardResolver $creditCardResolver,
        ExpirationDate $expirationDate
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->creditCardResolver = $creditCardResolver;
        $this->expirationDate = $expirationDate;
    }

    /**
     * Convert Stripe payment response into payment token
     *
     * @param StripePaymentResponse $paymentResponse
     * @return PaymentTokenInterface
     */
    public function convert($paymentResponse)
    {
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->tokenFactory->create();

        $token = isset($paymentResponse['payment_method']) ? $paymentResponse['payment_method'] : '';

        $paymentToken->setPaymentMethod('stripe_payments')
            ->setType(Token::TOKEN_TYPE_CARD)
            ->setTokenValue($token)
            ->setDetails('customerToken', isset($paymentResponse['customer']) ? $paymentResponse['customer'] : null);

        /** @var CreditCard|null $creditCard */
        $creditCard = $this->creditCardResolver->getByResponse($paymentResponse);
        if ($creditCard) {
            $paymentToken
                ->setDetails('typeCode', $creditCard->getType())
                ->setDetails('lastCcNumber', $creditCard->getLast4())
                ->setDetails('expirationDate', $creditCard->getExpMonth() . '/' . $creditCard->getExpYear())
                ->setExpiresAt(
                    $this->expirationDate->getFormatted($creditCard->getExpMonth(), $creditCard->getExpYear())
                );
        }

        return $paymentToken;
    }
}
