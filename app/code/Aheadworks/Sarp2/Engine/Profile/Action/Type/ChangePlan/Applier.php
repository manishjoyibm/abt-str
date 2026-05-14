<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionsRepository;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var Manager
     */
    private $notificationManager;

    /**
     * @param ResultFactory $validationResultFactory
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     * @param ProfileRepositoryInterface $profileRepository
     * @param PlanRepositoryInterface $planRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param Manager $notificationManager
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        SubscriptionOptionRepositoryInterface $optionsRepository,
        ProfileRepositoryInterface $profileRepository,
        PlanRepositoryInterface $planRepository,
        DataObjectProcessor $dataObjectProcessor,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        Manager $notificationManager
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->optionsRepository = $optionsRepository;
        $this->profileRepository = $profileRepository;
        $this->planRepository = $planRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->notificationManager = $notificationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $newPlanId = $action->getData()->getNewPlanId();
        $this->updateProfile($profile, $newPlanId);
        $this->updatePayments($profile);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ProfileInterface $profile, ActionInterface $action)
    {
        $newPlanId = $action->getData()->getNewPlanId();
        $isValid = $profile->getPlanId() != $newPlanId;
        $message = 'Selected plan is used in subscription.';

        if ($isValid) {
            foreach ($profile->getItems() as &$item) {
                if ($item->getParentItemId()) {
                    continue;
                }

                $newOption = $this->getOptionByPlan($item->getProductId(), $newPlanId);
                if (!$newOption) {
                    $isValid = false;
                    $message = 'Selected plan is not available in product.';
                    break;
                }
            }
        }

        $resultData = ['isValid' => $isValid];
        if (!$isValid) {
            $resultData['message'] = $message;
        }
        return $this->validationResultFactory->create($resultData);
    }

    /**
     * Retrieve get option by plan
     *
     * @param $productId
     * @param $newPlanId
     * @return SubscriptionOptionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOptionByPlan($productId, $newPlanId)
    {
        $subscriptionOptions = $this->optionsRepository->getList($productId);
        /** @var SubscriptionOptionInterface $option */
        foreach ($subscriptionOptions as $option) {
            if ($newPlanId == $option->getPlanId()) {
                return $option;
            }
        }
        return null;
    }

    /**
     * Update profile
     *
     * @param ProfileInterface $profile
     * @param int $newPlanId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateProfile($profile, $newPlanId)
    {
        $newPlan = $this->planRepository->get($newPlanId);
        $profile
            ->setPlanId($newPlanId)
            ->setPlanDefinitionId($newPlan->getDefinitionId())
            ->setPlanName($newPlan->getName());

        $planDefinition = $profile->getPlanDefinition();
        $planDefinition
            ->setIsInitialFeeEnabled(false)
            ->setIsTrialPeriodEnabled(false)
            ->setTrialTotalBillingCycles(0);
        $profile->setPlanDefinition($planDefinition);

        foreach ($profile->getItems() as &$item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $newOption = $this->getOptionByPlan($item->getProductId(), $newPlanId);
            $newPlanArray = $this->dataObjectProcessor->buildOutputDataArray(
                $newPlan,
                PlanInterface::class
            );
            $newOptionArray = $this->dataObjectProcessor->buildOutputDataArray(
                $newOption,
                SubscriptionOptionInterface::class
            );
            unset($newOptionArray[SubscriptionOptionInterface::PLAN]);
            unset($newOptionArray[SubscriptionOptionInterface::PRODUCT]);

            $productOptions = $item->getProductOptions();
            $productOptions['info_buyRequest']['aw_sarp2_subscription_type'] = $newOption->getOptionId();
            $productOptions['aw_sarp2_subscription_plan'] = $newPlanArray;
            $productOptions['aw_sarp2_subscription_option'] = $newOptionArray;
            $item->setProductOptions($productOptions);
            if ($item->hasChildItems()) {
                foreach ($item->getChildItems() as &$child) {
                    $childOptions = $child->getProductOptions();
                    $childOptions['info_buyRequest']['aw_sarp2_subscription_type'] = $newOption->getOptionId();
                    $childOptions['aw_sarp2_subscription_plan'] = $newPlanArray;
                    $childOptions['aw_sarp2_subscription_option'] = $newOptionArray;
                    $child->setProductOptions($productOptions);
                }
            }
        }
        $this->profileRepository->save($profile);
    }

    /**
     * Update payments
     *
     * @param ProfileInterface $profile
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function updatePayments($profile)
    {
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $payment->setSchedule($this->updateSchedule($profile, $payment->getSchedule()));

            $payment->setBaseTotalScheduled($profile->getBaseRegularGrandTotal());
            $payment->setTotalScheduled($profile->getRegularGrandTotal());
            $payment->setPaymentPeriod(PaymentInterface::PERIOD_REGULAR);
            $payment->setType(PaymentInterface::TYPE_PLANNED);
        }
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
            $this->notificationManager->reschedule(NotificationInterface::TYPE_UPCOMING_BILLING, $payments);
        }
    }

    /**
     * Update schedule
     *
     * @param ProfileInterface $profile
     * @param ScheduleInterface $schedule
     * @return ScheduleInterface
     */
    private function updateSchedule($profile, $schedule)
    {
        $profileDefinition = $profile->getProfileDefinition();
        $regularTotalCount =
            $profileDefinition->getIsMembershipModelEnabled() && $profileDefinition->getTotalBillingCycles()
                ? $profileDefinition->getTotalBillingCycles() + 1
                : $profileDefinition->getTotalBillingCycles();
        $schedule
            ->setPeriod($profileDefinition->getBillingPeriod())
            ->setFrequency($profileDefinition->getBillingFrequency())
            ->setTrialTotalCount(
                $profileDefinition->getIsTrialPeriodEnabled()
                    ? $profileDefinition->getTrialTotalBillingCycles()
                    : 0
            )
            ->setRegularTotalCount($regularTotalCount)
            ->setIsMembershipModel(
                $profileDefinition->getIsMembershipModelEnabled()
                    ? $profileDefinition->getIsMembershipModelEnabled()
                    : false
            );
        $schedule
            ->setIsInitialPaid(0)
            ->setTrialCount(0)
            ->setRegularCount(0);

        return $schedule;
    }
}
