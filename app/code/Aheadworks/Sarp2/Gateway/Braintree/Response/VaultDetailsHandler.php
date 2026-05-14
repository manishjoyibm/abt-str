<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree\Response;

use Aheadworks\Sarp2\Gateway\Braintree\SubjectReaderFactory;
use Aheadworks\Sarp2\Gateway\Braintree\TokenAssigner;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class VaultDetailsHandler
 * @package Aheadworks\Sarp2\Gateway\Braintree\Response
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @var TokenAssigner
     */
    private $tokenAssigner;

    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param TokenAssigner $tokenAssigner
     */
    public function __construct(
        SubjectReaderFactory $subjectReaderFactory,
        TokenAssigner $tokenAssigner
    ) {
        $this->subjectReaderFactory = $subjectReaderFactory;
        $this->tokenAssigner = $tokenAssigner;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $subjectReader = $this->subjectReaderFactory->getInstance();
        $paymentDO = $subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        $isPaymentTokenEnabled = $payment->getAdditionalInformation('is_aw_sarp_payment_token_enabled');
        if ($isPaymentTokenEnabled) {
            $transaction = $subjectReader->readTransaction($response);
            $this->tokenAssigner->assignByCreditCardDetails($payment, $transaction->creditCardDetails);
        }
    }
}
