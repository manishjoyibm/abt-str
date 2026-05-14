<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Request;

use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Observer\StripePaymentsRecurring\DataAssignObserver;
use Aheadworks\Sarp2\Gateway\StripePayments\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Aheadworks\Sarp2\Gateway\StripePayments\Config\Config as StripeConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class PaymentDataBuilder
 * @package Aheadworks\Sarp2\Gateway\StripePayments\Request
 */
class PaymentDataBuilder implements BuilderInterface
{
    /**#@+
     * Payment data
     */
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const DESCRIPTION = 'description';
    const METADATA = 'metadata';
    const PAYMENT_METHOD = 'payment_method';
    const CUSTOMER = 'customer';
    /**#@-*/

    /**
     * @var StripeConfig
     */
    private $stripeConfig;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @param StripeConfig $stripeConfig
     * @param SubjectReader $subjectReader
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        StripeConfig $stripeConfig,
        SubjectReader $subjectReader,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->stripeConfig = $stripeConfig;
        $this->subjectReader = $subjectReader;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();

        $params = $this->stripeConfig->getStripeParamsFromOrder($order);

        $result = [
            self::AMOUNT => $params['amount'],
            self::CURRENCY => $params['currency'],
            self::DESCRIPTION => $params['description'],
            self::METADATA => $params['metadata'],
        ];

        $paymentTokenId = $payment->getAdditionalInformation(DataAssignObserver::PAYMENT_TOKEN_ID);
        if (!$paymentTokenId) {
            throw new \LogicException('Payment token Id does not specified.');
        }

        $token = $this->paymentTokenRepository->get($paymentTokenId);
        $gatewayToken = $token->getTokenValue();
        $customerToken = $token->getDetails('customerToken');

        $result[self::PAYMENT_METHOD] = $gatewayToken;
        $result[self::CUSTOMER] = $customerToken;

        return $result;
    }
}
