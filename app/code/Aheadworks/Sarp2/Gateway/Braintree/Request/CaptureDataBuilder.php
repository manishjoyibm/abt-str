<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree\Request;

use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Observer\BraintreeRecurring\DataAssignObserver;
use Aheadworks\Sarp2\Gateway\Braintree\SubjectReaderFactory;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class CaptureDataBuilder
 * @package Aheadworks\Sarp2\Gateway\Braintree\Request
 */
class CaptureDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        SubjectReaderFactory $subjectReaderFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->subjectReaderFactory = $subjectReaderFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $subjectReader = $this->subjectReaderFactory->getInstance();
        $paymentDO = $subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $paymentTokenId = $payment->getAdditionalInformation(DataAssignObserver::GATEWAY_TOKEN);
        if (!$paymentTokenId) {
            throw new \LogicException('Payment token Id does not specified.');
        }
        $gatewayToken = $this->paymentTokenRepository->get($paymentTokenId)
            ->getTokenValue();
        return [
            'amount' => $this->formatPrice($subjectReader->readAmount($buildSubject)),
            'paymentMethodToken' => $gatewayToken
        ];
    }
}
