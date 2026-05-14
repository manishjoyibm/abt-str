<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Notification\Scheduler;

use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\SchedulerInterface;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\BillingFailed;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\BillingSuccessful;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Type\UpcomingBilling;

/**
 * Class Pool
 * @package Aheadworks\Sarp2\Engine\Notification\Scheduler
 */
class Pool
{
    /**
     * @var array
     */
    private $schedulers = [
        NotificationInterface::TYPE_BILLING_SUCCESSFUL => BillingSuccessful::class,
        NotificationInterface::TYPE_BILLING_FAILED => BillingFailed::class,
        NotificationInterface::TYPE_UPCOMING_BILLING => UpcomingBilling::class
    ];

    /**
     * @var SchedulerInterface[]
     */
    private $schedulerInstances = [];

    /**
     * @var Factory
     */
    private $schedulerFactory;

    /**
     * @param Factory $schedulerFactory
     * @param array $schedulers
     */
    public function __construct(
        Factory $schedulerFactory,
        array $schedulers = []
    ) {
        $this->schedulerFactory = $schedulerFactory;
        $this->schedulers = array_merge($this->schedulers, $schedulers);
    }

    /**
     * Get notification scheduler of specified type
     *
     * @param string $type
     * @return SchedulerInterface
     * @throws \Exception
     */
    public function getScheduler($type)
    {
        if (!isset($this->schedulerInstances[$type])) {
            if (!isset($this->schedulers[$type])) {
                throw new \Exception(sprintf('Unknown notification scheduler: %s requested', $type));
            }
            $this->schedulerInstances[$type] = $this->schedulerFactory->create($this->schedulers[$type]);
        }
        return $this->schedulerInstances[$type];
    }
}
