<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Abbott\Sarp2\Model\Product\Subscription\Option\Source;

use Aheadworks\Sarp2\Model\Plan\Checker as PlanChecker;
use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Abbott\Sarp2\Model\Product\Subscription\Option\Finder as SubscriptionOptionFinder;

/**
 * Class Backend
 * @package Abbott\Sarp2\Model\Product\Subscription\Option\Source
 */
class Backend
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var SubscriptionOptionFinder
     */
    private $subscriptionOptionFinder;

    /**
     * @var PlanChecker
     */
    private $planChecker;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param SubscriptionOptionFinder $subscriptionOptionFinder
     * @param PlanChecker $planChecker
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        SubscriptionOptionFinder $subscriptionOptionFinder,
        PlanChecker $planChecker
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->subscriptionOptionFinder = $subscriptionOptionFinder;
        $this->planChecker = $planChecker;
    }

    /**
     * Get frontend options
     *
     * @param int $productId
	 * @param int $storeId
     * @return array
     */
    public function getOptionArray($productId, $storeId)
    {
        $optionArray = [];
        if (!$this->isSubscriptionChecker->checkById($productId, true)) {
            $optionArray[0] = __('One-off purchase (No subscription)');
        }

        $options = $this->subscriptionOptionFinder->getSortedOptions($productId, $storeId);
        foreach ($options as $option) {
            if ($this->planChecker->isEnabled($option->getPlanId())) {
                $optionArray[$option->getOptionId()] = $option->getFrontendTitle();
            }
        }

        return $optionArray;
    }

    /**
     * Get frontend options for plan selection
     *
     * @param int $productId
     * @return array
     */
    public function getPlanOptionArray($productId, $storeId)
    {
        $optionArray = [];
        $options = $this->subscriptionOptionFinder->getSortedOptions($productId, $storeId);
        foreach ($options as $option) {
            if ($this->planChecker->isEnabled($option->getPlanId())) {
                $optionArray[$option->getPlanId()] = $option->getFrontendTitle();
            }
        }

        return $optionArray;
    }
}
