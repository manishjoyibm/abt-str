<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Response;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Aheadworks\Sarp2\Gateway\Braintree\SubjectReaderFactory;

/**
 * Class TransactionIdHandler
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Response
 */
class TransactionIdHandler implements HandlerInterface
{
    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReaderFactory $subjectReaderFactory,
        SubjectReader $subjectReader
    ) {
        $this->subjectReaderFactory = $subjectReaderFactory;
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
        $command = $this->subjectReader->readCommand($handlingSubject);
        $payment = $paymentDO->getPayment();
        $subjectReaderBraintree = $this->subjectReaderFactory->getInstance();
        $transaction = $subjectReaderBraintree->readTransaction($response);

        $transactionId = $transaction->id;
        $payment->setLastTransactionId($transactionId);
        $payment->setAdditionalInformation($command . '_txn_id', $transactionId);
    }
}
