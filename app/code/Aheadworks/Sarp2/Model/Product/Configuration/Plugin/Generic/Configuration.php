<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Configuration\Plugin\Generic;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Subscription\Configuration as SubscriptionConfiguration;
use Magento\Catalog\Helper\Product\Configuration as ProductConfiguration;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Class Configuration
 * @package Aheadworks\Sarp2\Model\Product\Configuration\Plugin\Generic
 */
class Configuration
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var SubscriptionConfiguration
     */
    private $subscriptionConfiguration;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param SubscriptionConfiguration $subscriptionConfiguration
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        SubscriptionConfiguration $subscriptionConfiguration
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->subscriptionConfiguration = $subscriptionConfiguration;
    }

    /**
     * @param ProductConfiguration $subject
     * @param array $options
     * @param ItemInterface $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomOptions(ProductConfiguration $subject, array $options, ItemInterface $item)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        return $this->isSubscriptionChecker->check($item)
            ? array_merge($options, $this->subscriptionConfiguration->getOptions($item))
            : $options;
    }
}
