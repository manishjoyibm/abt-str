<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Schedule;

use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Engine\Payment\ScheduleFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Schedule as ScheduleResource;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Persistence
 * @package Aheadworks\Sarp2\Engine\Payment\Schedule
 */
class Persistence
{
    /**
     * @var ScheduleResource
     */
    private $resource;

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var array
     */
    private $instancesById = [];

    /**
     * @param ScheduleResource $resource
     * @param ScheduleFactory $scheduleFactory
     */
    public function __construct(
        ScheduleResource $resource,
        ScheduleFactory $scheduleFactory
    ) {
        $this->resource = $resource;
        $this->scheduleFactory = $scheduleFactory;
    }

    /**
     * Retrieve schedule instance
     *
     * @param int $scheduleId
     * @return Schedule
     * @throws NoSuchEntityException
     */
    public function get($scheduleId)
    {
        if (!isset($this->instancesById[$scheduleId])) {
            /** @var Schedule $schedule */
            $schedule = $this->scheduleFactory->create();
            $this->resource->load($schedule, $scheduleId);
            if (!$schedule->getScheduleId()) {
                throw NoSuchEntityException::singleField('scheduleId', $scheduleId);
            }
            $this->instancesById[$scheduleId] = $schedule;
        }
        return $this->instancesById[$scheduleId];
    }
}
