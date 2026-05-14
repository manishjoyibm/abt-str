<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Finder;
use Aheadworks\Sarp2\PaymentData\StripePayments\PaymentIntent\ToToken as PaymentIntentToToken;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Stripe\PaymentIntent as StripePaymentIntent;

/**
 * Class TokenAssigner
 * @package Aheadworks\Sarp2\Gateway\StripePayments
 */
class TokenAssigner
{
    /**
     * @var Finder
     */
    private $tokenFinder;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var PaymentIntentToToken
     */
    private $paymentIntentToTokenConverter;

    /**
     * @param Finder $tokenFinder
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param PaymentIntentToToken $paymentIntentToTokenConverter
     */
    public function __construct(
        Finder $tokenFinder,
        PaymentTokenRepositoryInterface $tokenRepository,
        PaymentIntentToToken $paymentIntentToTokenConverter
    ) {
        $this->tokenFinder = $tokenFinder;
        $this->tokenRepository = $tokenRepository;
        $this->paymentIntentToTokenConverter = $paymentIntentToTokenConverter;
    }

    /**
     * Assign payment token using credit card details
     *
     * @param OrderPaymentInterface $payment
     * @param StripePaymentIntent $paymentIntent
     * @return OrderPaymentInterface
     */
    public function assignBypaymentIntent($payment, $paymentIntent)
    {
        $token = $this->getTokenToAssign(
            $this->paymentIntentToTokenConverter->convert($paymentIntent)
        );
        $payment->setAdditionalInformation('aw_sarp_payment_token_id', $token->getTokenId());

        return $payment;
    }

    /**
     * Get token to assign
     *
     * @param PaymentTokenInterface $candidate
     * @return PaymentTokenInterface
     */
    private function getTokenToAssign($candidate)
    {
        $existing = $this->tokenFinder->findExisting($candidate);
        if (!$existing) {
            $candidate->setIsActive(true);
            $this->tokenRepository->save($candidate);
            return $candidate;
        }
        return $existing;
    }
}
