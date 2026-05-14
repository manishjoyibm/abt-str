<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Response;

use Aheadworks\Sarp2\Gateway\StripePayments\SubjectReader;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\CreditCard;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\CreditCardResolver;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CardDetailsHandler
 * @package Aheadworks\Sarp2\Gateway\StripePayments\Response
 */
class CardDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CreditCardResolver
     */
    private $creditCardResolver;

    /**
     * @param SubjectReader $subjectReader
     * @param CreditCardResolver $creditCardResolver
     */
    public function __construct(
        SubjectReader $subjectReader,
        CreditCardResolver $creditCardResolver
    ) {
        $this->subjectReader = $subjectReader;
        $this->creditCardResolver = $creditCardResolver;
    }

    /**
     * Handles fraud messages
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        /** @var Response $response */
        $response = $this->subjectReader->readTransactionResponse($response);

        /** @var CreditCard|null $creditCard */
        $creditCard = $this->creditCardResolver->getByResponse($response);
        if ($creditCard) {
            $payment->setCcType($creditCard->getType());
            $payment->setCcLast4($creditCard->getLast4());
            $payment->setCcExpMonth($creditCard->getExpMonth());
            $payment->setCcExpYear($creditCard->getExpYear());
        }
    }
}
