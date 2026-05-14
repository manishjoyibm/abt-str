<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Finder;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response as StripePaymentResponse;
use Aheadworks\Sarp2\PaymentData\StripePayments\PaymentResponse\ToToken as PaymentResponseToToken;
use Magento\Payment\Model\InfoInterface;

/**
 * Class TokenAssigner
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments
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
     * @var PaymentResponseToToken
     */
    private $paymentResponseToTokenConverter;

    /**
     * @param Finder $tokenFinder
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param PaymentResponseToToken $paymentResponseToTokenConverter
     */
    public function __construct(
        Finder $tokenFinder,
        PaymentTokenRepositoryInterface $tokenRepository,
        PaymentResponseToToken $paymentResponseToTokenConverter
    ) {
        $this->tokenFinder = $tokenFinder;
        $this->tokenRepository = $tokenRepository;
        $this->paymentResponseToTokenConverter = $paymentResponseToTokenConverter;
    }

    /**
     * Assign payment token using Stripe response data
     *
     * @param InfoInterface $payment
     * @param StripePaymentResponse $paymentResponse
     * @return InfoInterface
     */
    public function assignByPaymentResponse($payment, $paymentResponse)
    {
        $token = $this->getTokenToAssign(
            $this->paymentResponseToTokenConverter->convert($paymentResponse)
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
