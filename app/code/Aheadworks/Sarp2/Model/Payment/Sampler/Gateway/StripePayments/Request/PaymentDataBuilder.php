<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\CustomerId as StripeSamplerCustomerId;

/**
 * Class PaymentDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\Request
 */
class PaymentDataBuilder implements BuilderInterface
{
    /**#@+
     * Payment data
     */
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const PAYMENT_METHOD = 'payment_method';
    const CUSTOMER = 'customer';
    /**#@-*/

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var StripeSamplerCustomerId
     */
    private $stripeSamplerCustomerId;

    /**
     * @param SubjectReader $subjectReader
     * @param StripeSamplerCustomerId $stripeSamplerCustomerId
     */
    public function __construct(
        SubjectReader $subjectReader,
        StripeSamplerCustomerId $stripeSamplerCustomerId
    ) {
        $this->subjectReader = $subjectReader;
        $this->stripeSamplerCustomerId = $stripeSamplerCustomerId;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();

        $integerAmount = $payment->getAmount() * 100;
        $paymentToken = $payment->getAdditionalInformation('token');

        $customerId = $payment->getAdditionalInformation('customer_id');
        if (empty($customerId)) {
            $customerId = $this->stripeSamplerCustomerId->resolve($payment->getProfile());
        }

        $result = [
            self::AMOUNT => $integerAmount,
            self::CURRENCY => $payment->getCurrencyCode(),
            self::PAYMENT_METHOD => $paymentToken,
        ];

        if (!empty($customerId)) {
            $result[self::CUSTOMER] = $customerId;
        }

        return $result;
    }
}
