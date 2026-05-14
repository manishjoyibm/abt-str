<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\Request;

use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Observer\BraintreeRecurring\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;
// use Magento\AuthorizenetAcceptjs\Gateway\SubjectReaderFactory;
// use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;

/**
 * Class PaymentDataBuilder
 * @package Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\Request
 */
class PaymentDataBuilder implements BuilderInterface
{
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
        // SubjectReaderFactory $subjectReaderFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        // $this->subjectReaderFactory = $subjectReaderFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        /** @var SubjectReader $subjectReader */
        // $subjectReader = $this->subjectReaderFactory->create();
        // $paymentDO = $subjectReader->readPayment($buildSubject);
        // $payment = $paymentDO->getPayment();

        // $paymentTokenId = $payment->getAdditionalInformation(DataAssignObserver::PAYMENT_TOKEN_ID);
        // if (!$paymentTokenId) {
        //     throw new \LogicException('Payment token Id does not specified.');
        // }
        // $paymentToken = $this->paymentTokenRepository->get($paymentTokenId);

        // return [
        //     'transactionRequest' => [
        //         'profile' => [
        //             'customerProfileId' => $paymentToken->getDetails()['customerProfileId'],
        //             'paymentProfile' => [
        //                 'paymentProfileId' => $paymentToken->getTokenValue()
        //             ]
        //         ]
        //     ]
        // ];
    }
}
