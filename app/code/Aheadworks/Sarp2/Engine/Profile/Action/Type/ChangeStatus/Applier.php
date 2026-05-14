<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus
 */
class Applier implements ApplierInterface
{
    /**
     * @var StatusMap
     */
    private $statusMap;

    /**
     * @var Status
     */
    private $statusSource;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @param StatusMap $statusMap
     * @param Status $statusSource
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param ResultFactory $validationResultFactory
     */
    public function __construct(
        StatusMap $statusMap,
        Status $statusSource,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        ResultFactory $validationResultFactory
    ) {
        $this->statusMap = $statusMap;
        $this->statusSource = $statusSource;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $status = $action->getData()->getStatus();
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $payment->getSchedule()->setIsReactivated($status == Status::ACTIVE);
        }
        $profile->setStatus($status);
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ProfileInterface $profile, ActionInterface $action)
    {
        $status = $action->getData()->getStatus();
        $profileStatus = $this->getProfileStatus($profile);
        $allowedStatuses = $this->statusMap->getAllowedStatuses($profileStatus);

        $isSuspendedActivation = $status == Status::ACTIVE && $profileStatus == Status::SUSPENDED;
        if ($isSuspendedActivation) {
            $isValid = in_array($status, $allowedStatuses);
            $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
            foreach ($payments as $payment) {
                if ($payment->getType() == PaymentInterface::TYPE_REATTEMPT) {
                    $isValid = false;
                }
            }
        } else {
            $isValid = $status == $profileStatus || in_array($status, $allowedStatuses);
        }

        $resultData = ['isValid' => $isValid];
        if (!$isValid) {
            $statusOptions = $this->statusSource->getOptions();
            $resultData['message'] = $isSuspendedActivation
                ? 'Unable to perform activation action, subscription suspended due to payment failures.'
                : 'Profile status ' . $statusOptions[$status] . ' is not allowed.';
        }
        return $this->validationResultFactory->create($resultData);
    }

    /**
     * Get profile status
     *
     * @param ProfileInterface|Profile $profile
     * @return string
     */
    private function getProfileStatus($profile)
    {
        return $profile->getOrigData('status') != $profile->getStatus()
            ? $profile->getOrigData('status')
            : $profile->getStatus();
    }
}
