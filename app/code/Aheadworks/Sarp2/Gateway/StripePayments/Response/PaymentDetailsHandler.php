<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Response;

use Aheadworks\Sarp2\Gateway\StripePayments\SubjectReader;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Aheadworks\Sarp2\Observer\StripePaymentsRecurring\DataAssignObserver;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class PaymentDetailsHandler
 * @package Aheadworks\Sarp2\Gateway\StripePayments\Response
 */
class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
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

        $payment->setCcTransId($response->getId());
        $payment->setLastTransId($response->getId());
        $payment->setIsTransactionClosed(0);
        $payment->setIsFraudDetected(false);

        $payment->unsAdditionalInformation(DataAssignObserver::PAYMENT_TOKEN_ID);
    }
}
