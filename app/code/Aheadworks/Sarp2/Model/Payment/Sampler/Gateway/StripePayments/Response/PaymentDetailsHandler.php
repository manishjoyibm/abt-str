<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Aheadworks\Sarp2\Gateway\StripePayments\SubjectReader as StripePaymentsSubjectReader;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\TokenAssigner
    as StripeSamplerTokenAssigner;

/**
 * Class PaymentDetailsHandler
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\Response
 */
class PaymentDetailsHandler implements HandlerInterface
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
     * @var StripeSamplerTokenAssigner
     */
    private $stripeSamplerTokenAssigner;

    /**
     * @param SubjectReader $subjectReader
     * @param StripePaymentsSubjectReader $stripePaymentsSubjectReader
     * @param StripeSamplerTokenAssigner $stripeSamplerTokenAssigner
     */
    public function __construct(
        SubjectReader $subjectReader,
        StripePaymentsSubjectReader $stripePaymentsSubjectReader,
        StripeSamplerTokenAssigner $stripeSamplerTokenAssigner
    ) {
        $this->subjectReader = $subjectReader;
        $this->stripePaymentsSubjectReader = $stripePaymentsSubjectReader;
        $this->stripeSamplerTokenAssigner = $stripeSamplerTokenAssigner;
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

        /** @var SamplerInfoInterface $payment */
        $payment = $paymentDO->getPayment();
        /** @var Response $transactionResponse */
        $transactionResponse = $this->stripePaymentsSubjectReader->readTransactionResponse($response);

        $this->stripeSamplerTokenAssigner->assignByPaymentResponse($payment, $transactionResponse);
    }
}
