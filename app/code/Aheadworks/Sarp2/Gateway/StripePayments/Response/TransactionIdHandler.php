<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Response;

use Aheadworks\Sarp2\Gateway\StripePayments\SubjectReader;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionIdHandler
 * @package Aheadworks\BamboraApac\Gateway\Response
 */
class TransactionIdHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handles response
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
        /** @var Response $transactionResponse */
        $transactionResponse = $this->subjectReader->readTransactionResponse($response);

        $this->setTransactionId($payment, $transactionResponse);
        $payment->setIsTransactionClosed($this->shouldCloseTransaction());
        $payment->setShouldCloseParentTransaction($this->shouldCloseParentTransaction($payment));
    }

    /**
     * Set transaction id
     *
     * @param Payment $payment
     * @param Response $transactionResponse
     * @return void
     */
    protected function setTransactionId($payment, $transactionResponse)
    {
        $transactionId = $transactionResponse->getId();
        $payment->setTransactionId($transactionId);
    }

    /**
     * Retrieve transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return false;
    }

    /**
     * Retrieve parent transaction should be closed
     *
     * @param Payment $payment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction($payment)
    {
        return false;
    }
}
