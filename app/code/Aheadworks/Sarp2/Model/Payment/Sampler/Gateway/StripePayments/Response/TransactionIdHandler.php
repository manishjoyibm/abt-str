<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Aheadworks\Sarp2\Gateway\StripePayments\SubjectReader as StripePaymentsSubjectReader;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;

/**
 * Class TransactionIdHandler
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\Response
 */
class TransactionIdHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var StripePaymentsSubjectReader
     */
    private $stripePaymentsSubjectReader;

    /**
     * @param SubjectReader $subjectReader
     * @param StripePaymentsSubjectReader $stripePaymentsSubjectReader
     */
    public function __construct(
        SubjectReader $subjectReader,
        StripePaymentsSubjectReader $stripePaymentsSubjectReader
    ) {
        $this->subjectReader = $subjectReader;
        $this->stripePaymentsSubjectReader = $stripePaymentsSubjectReader;
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
        $command = $this->subjectReader->readCommand($handlingSubject);

        /** @var SamplerInfoInterface $payment */
        $payment = $paymentDO->getPayment();
        /** @var Response $transactionResponse */
        $transactionResponse = $this->stripePaymentsSubjectReader->readTransactionResponse($response);

        $transactionId = $transactionResponse->getId();
        $payment->setLastTransactionId($transactionId);
        $payment->setAdditionalInformation($command . '_txn_id', $transactionId);
    }
}
