<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Response;

use Aheadworks\Sarp2\Gateway\Braintree\SubjectReaderFactory;
use Aheadworks\Sarp2\Gateway\Braintree\TokenAssigner;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class VaultDetailsHandler
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Response
 */
class VaultDetailsHandler implements HandlerInterface
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
     * @var TokenAssigner
     */
    private $tokenAssigner;

    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param SubjectReader $subjectReader
     * @param TokenAssigner $tokenAssigner
     */
    public function __construct(
        SubjectReaderFactory $subjectReaderFactory,
        SubjectReader $subjectReader,
        TokenAssigner $tokenAssigner
    ) {
        $this->subjectReaderFactory = $subjectReaderFactory;
        $this->subjectReader = $subjectReader;
        $this->tokenAssigner = $tokenAssigner;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        $subjectReaderBraintree = $this->subjectReaderFactory->getInstance();
        $transaction = $subjectReaderBraintree->readTransaction($response);
        $this->tokenAssigner->assignByCreditCardDetails($payment, $transaction->creditCardDetails);

        //remove previously set data
        $payment->unsAdditionalInformation('payment_method_nonce');
        $payment->unsAdditionalInformation('is_active_payment_token_enabler');
    }
}
