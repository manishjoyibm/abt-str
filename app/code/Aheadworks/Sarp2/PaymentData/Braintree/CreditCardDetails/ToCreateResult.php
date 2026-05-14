<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails;

use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\PaymentData\Create\Result;
use Aheadworks\Sarp2\PaymentData\Create\ResultFactory;
use Braintree\CreditCard;

/**
 * Class ToCreateResult
 * @package Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails
 */
class ToCreateResult
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ExpirationDate
     */
    private $expirationDate;

    /**
     * @var Type
     */
    private $type;

    /**
     * @param ResultFactory $resultFactory
     * @param ExpirationDate $expirationDate
     * @param Type $type
     */
    public function __construct(
        ResultFactory $resultFactory,
        ExpirationDate $expirationDate,
        Type $type
    ) {
        $this->resultFactory = $resultFactory;
        $this->expirationDate = $expirationDate;
        $this->type = $type;
    }

    /**
     * Convert credit card detail into create token result
     *
     * @param CreditCard $creditCard
     * @return Result
     */
    public function convert($creditCard)
    {
        return $this->resultFactory->create(
            [
                'tokenType' => Token::TOKEN_TYPE_CARD,
                'gatewayToken' => $creditCard->token,
                'additionalData' => [
                    'expiration_date'               => $this->expirationDate->getFormatted($creditCard),
                    'credit_card_type'              => $this->type->getPrepared($creditCard),
                    'credit_card_masked_number'     => $creditCard->last4,
                    'credit_card_expiration_date'   => $creditCard->expirationDate,
                ]
            ]
        );
    }
}
