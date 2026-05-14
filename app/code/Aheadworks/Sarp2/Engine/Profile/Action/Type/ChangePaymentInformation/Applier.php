<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePaymentInformation;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Model\Payment\SamplerManagement;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePaymentInformation
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var SamplerManagement
     */
    private $samplerManagement;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param ResultFactory $validationResultFactory
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param SamplerManagement $samplerManagement
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        SamplerManagement $samplerManagement,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->samplerManagement = $samplerManagement;
        $this->profileRepository = $profileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $payment = $action->getData()->getPayment();
        $billingAddress = $action->getData()->getBillingAddress();

        if ($billingAddress !== null) {
            $profile->setBillingAddress($billingAddress);
        }

        $samplerInfo = $this->samplerManagement->submitPayment($profile, $payment);
        $additionalInformation = $samplerInfo->getAdditionalInformation();
        $paymentTokenId = $additionalInformation['aw_sarp_payment_token_id'];

        $profile->setPaymentTokenId($paymentTokenId);
        $profile->setPaymentMethod($samplerInfo->getMethod());
        $this->profileRepository->save($profile);

        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $paymentData = $payment->getPaymentData();
            $paymentData['token_id'] = $profile->getPaymentTokenId();
            $payment->setPaymentData($paymentData);
        }
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ProfileInterface $profile, ActionInterface $action)
    {
        $isValid = true;

        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            if ($payment->getType() == PaymentInterface::TYPE_LAST_PERIOD_HOLDER) {
                $isValid = false;
                $message = 'Payment details cannot be changed after all payments are done.';
            }
        }

        $resultData = ['isValid' => $isValid];
        if (!$isValid) {
            $resultData['message'] = $message;
        }
        return $this->validationResultFactory->create($resultData);
    }
}
