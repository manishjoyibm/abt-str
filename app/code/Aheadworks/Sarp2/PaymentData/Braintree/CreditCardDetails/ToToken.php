<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Braintree\CreditCard;

/**
 * Class ToToken
 * @package Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails
 */
class ToToken
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var ToCreateResult
     */
    private $toCreateResult;

    /**
     * @param PaymentTokenInterfaceFactory $tokenFactory
     * @param ToCreateResult $toCreateResult
     */
    public function __construct(
        PaymentTokenInterfaceFactory $tokenFactory,
        ToCreateResult $toCreateResult
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->toCreateResult = $toCreateResult;
    }

    /**
     * Convert credit card detail into payment token
     *
     * @param CreditCard $creditCard
     * @return PaymentTokenInterface
     */
    public function convert($creditCard)
    {
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->tokenFactory->create();
        $paymentData = $this->toCreateResult->convert($creditCard);
        $paymentToken->setPaymentMethod('braintree')
            ->setType($paymentData->getTokenType())
            ->setTokenValue($paymentData->getGatewayToken())
            ->setDetails('type', $paymentData->getAdditionalData()->getData('credit_card_type'))
            ->setDetails('maskedCC', $paymentData->getAdditionalData()->getData('credit_card_masked_number'))
            ->setDetails('expirationDate', $paymentData->getAdditionalData()->getData('credit_card_expiration_date'))
            ->setExpiresAt($paymentData->getAdditionalData()->getData('expiration_date'));
        return $paymentToken;
    }
}
