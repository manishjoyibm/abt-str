<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Source\Status as PlanStatus;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Checker
 * @package Aheadworks\Sarp2\Model\Plan
 */
class Checker
{
    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @param PlanRepositoryInterface $planRepository
     */
    public function __construct(
        PlanRepositoryInterface $planRepository
    ) {
        $this->planRepository = $planRepository;
    }

    /**
     * Checks if the plan is enabled
     *
     * @param int $planId
     * @return bool
     */
    public function isEnabled($planId)
    {
        try {
            $plan = $this->planRepository->get($planId);
            $result = $plan->getStatus() == PlanStatus::ENABLED;
        } catch (LocalizedException $e) {
            $result = false;
        }

        return $result;
    }
}
