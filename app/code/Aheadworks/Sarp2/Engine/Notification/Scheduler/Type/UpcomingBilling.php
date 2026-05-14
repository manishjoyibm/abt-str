<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Notification\Scheduler\Type;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationFactory;
use Aheadworks\Sarp2\Engine\Notification\SchedulerInterface;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\DataResolver;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubjectFactory;
use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Aheadworks\Sarp2\Model\Config;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime;
use \Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class UpcomingBilling
 * @package Aheadworks\Sarp2\Engine\Notification\Scheduler\Type
 */
class UpcomingBilling implements SchedulerInterface
{
    /**
     * @var NotificationFactory
     */
    private $notificationFactory;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResolveSubjectFactory
     */
    private $resolveSubjectFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @param NotificationFactory $notificationFactory
     * @param Persistence $persistence
     * @param DataResolver $dataResolver
     * @param Config $config
     * @param ResolveSubjectFactory $resolveSubjectFactory
     * @param DateTime $dateTime
     * @param CoreDate $coreDate
     */
    public function __construct(
        NotificationFactory $notificationFactory,
        Persistence $persistence,
        DataResolver $dataResolver,
        Config $config,
        ResolveSubjectFactory $resolveSubjectFactory,
        DateTime $dateTime,
        CoreDate $coreDate
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->persistence = $persistence;
        $this->dataResolver = $dataResolver;
        $this->config = $config;
        $this->resolveSubjectFactory = $resolveSubjectFactory;
        $this->dateTime = $dateTime;
        $this->coreDate = $coreDate;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule($sourcePayment)
    {
        $profile = $sourcePayment->getProfile();
        $storeId = $profile->getStoreId();
        $profileDefinition = $profile->getProfileDefinition();

        $offset = $profileDefinition->getUpcomingBillingEmailOffset()
            ? : $this->config->getUpcomingBillingEmailOffset($storeId);
        if ($offset && $sourcePayment->getType() != PaymentInterface::TYPE_LAST_PERIOD_HOLDER) {
            $estimated = (new \DateTime($sourcePayment->getScheduledAt()))
                ->modify('-' . $offset . ' day');
            $estimatedTm = $this->coreDate->gmtTimestamp($estimated);
            $today = $this->dateTime->formatDate(true);
            $todayTm = $this->coreDate->gmtTimestamp($today);
            if ($estimatedTm >= $todayTm) {
                /** @var Notification $notification */
                $notification = $this->notificationFactory->create();
                $notification->setType(NotificationInterface::TYPE_UPCOMING_BILLING)
                    ->setStatus(NotificationInterface::STATUS_READY)
                    ->setEmail($profile->getCustomerEmail())
                    ->setName($profile->getCustomerFullname())
                    ->setScheduledAt($estimated)
                    ->setStoreId($storeId)
                    ->setProfileId($sourcePayment->getProfileId());

                /** @var ResolveSubject $resolveSubject */
                $resolveSubject = $this->resolveSubjectFactory->create(
                    [
                        'sourcePayment' => $sourcePayment,
                        'nextPayments' => [$sourcePayment]
                    ]
                );
                $notification->setNotificationData($this->dataResolver->resolve($resolveSubject));

                try {
                    $this->persistence->save($notification);
                    return $notification;
                } catch (CouldNotSaveException $exception) {
                }
            }
        }

        return null;
    }
}
