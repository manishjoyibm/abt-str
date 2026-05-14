<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Order\Item\Option;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\TitleResolver as PlanTitleResolver;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Processor
 * @package Aheadworks\Sarp2\Model\Sales\Order\Item\Option
 */
class Processor
{
    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var PlanTitleResolver
     */
    private $planTitleResolver;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param PlanInterfaceFactory $planFactory
     * @param PlanTitleResolver $planTitleResolver
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        PlanInterfaceFactory $planFactory,
        PlanTitleResolver $planTitleResolver,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->planFactory = $planFactory;
        $this->planTitleResolver = $planTitleResolver;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Check if an order item is a subscription
     *
     * @param array $options
     * @return bool
     */
    public function isSubscription($options)
    {
        if (isset($options['aw_sarp2_subscription_plan'])
            && is_array($options['aw_sarp2_subscription_plan'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get detailed subscription options
     *
     * @param array $options
     * @param int $storeId
     * @param bool $isAdmin
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDetailedSubscriptionOptions($options, $storeId, $isAdmin = false)
    {
        $subscriptionOptions = [];
        if ($this->isSubscription($options)) {
            $planData = $options['aw_sarp2_subscription_plan'];
            /** @var PlanInterface $plan */
            $plan = $this->planFactory->create();
            $this->dataObjectHelper->populateWithArray($plan, $planData, PlanInterface::class);

            $planTitle = $isAdmin ? $plan->getName() : $this->planTitleResolver->getTitle($plan, $storeId);

            $subscriptionOptions[] = [
                'label' => __('Subscription Plan'),
                'value' => $planTitle,
                'aw_sarp2_subscription_plan' => $plan->getPlanId(),
            ];
        }

        return $subscriptionOptions;
    }

    /**
     * Remove subscription options
     *
     * @param array $options
     * @return array
     */
    public function removeSubscriptionOptions(&$options)
    {
        foreach ($options as $index => $optionData) {
            if (isset($optionData['aw_sarp2_subscription_plan'])) {
                unset($options[$index]);
            }
        }

        return $options;
    }
}
