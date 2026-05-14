<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Finder;
use Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails\ToToken as CreditCardToToken;
use Aheadworks\Sarp2\PaymentData\Braintree\PayPalDetails\ToToken as PayPalToToken;
use Braintree\CreditCard;
use Braintree\Transaction\PayPalDetails;
use Magento\Payment\Model\InfoInterface;

/**
 * Class TokenAssigner
 * @package Aheadworks\Sarp2\Gateway\Braintree
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
     * @var CreditCardToToken
     */
    private $ccToTokenConverter;

    /**
     * @var PayPalToToken
     */
    private $payPalToTokenConverter;

    /**
     * @param Finder $tokenFinder
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param CreditCardToToken $ccToTokenConverter
     * @param PayPalToToken $payPalToTokenConverter
     */
    public function __construct(
        Finder $tokenFinder,
        PaymentTokenRepositoryInterface $tokenRepository,
        CreditCardToToken $ccToTokenConverter,
        PayPalToToken $payPalToTokenConverter
    ) {
        $this->tokenFinder = $tokenFinder;
        $this->tokenRepository = $tokenRepository;
        $this->ccToTokenConverter = $ccToTokenConverter;
        $this->payPalToTokenConverter = $payPalToTokenConverter;
    }

    /**
     * Assign payment token using credit card details
     *
     * @param InfoInterface $payment
     * @param CreditCard $creditCardDetails
     * @return InfoInterface
     */
    public function assignByCreditCardDetails(InfoInterface $payment, $creditCardDetails)
    {
        $token = $this->getTokenToAssign(
            $this->ccToTokenConverter->convert($creditCardDetails)
        );
        $payment->setAdditionalInformation('aw_sarp_payment_token_id', $token->getTokenId());
        return $payment;
    }

    /**
     * Assign payment token using PayPal details
     *
     * @param InfoInterface $payment
     * @param PayPalDetails $payPalDetails
     * @return InfoInterface
     */
    public function assignByPayPalDetails(InfoInterface $payment, $payPalDetails)
    {
        $token = $this->getTokenToAssign(
            $this->payPalToTokenConverter->convert($payPalDetails)
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
