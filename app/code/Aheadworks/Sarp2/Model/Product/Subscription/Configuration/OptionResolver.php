<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Configuration;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Class OptionResolver
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Configuration
 */
class OptionResolver
{
    /**
     * @var SubscriptionOptionInterfaceFactory
     */
    private $subscriptionOptionFactory;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @param SubscriptionOptionInterfaceFactory $subscriptionOptionFactory
     * @param TaxConfig $taxConfig
     */
    public function __construct(
        SubscriptionOptionInterfaceFactory $subscriptionOptionFactory,
        TaxConfig $taxConfig
    ) {
        $this->subscriptionOptionFactory = $subscriptionOptionFactory;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Get subscription option
     *
     * @param AbstractItem $item
     * @param PlanInterface $plan
     * @return SubscriptionOptionInterface
     */
    public function getSubscriptionOption($item, $plan)
    {
        $product = $item->getProduct();
        $subscriptionOption = $this->subscriptionOptionFactory->create();

        $subscriptionOption
            ->setPlan($plan)
            ->setPlanId($plan->getPlanId())
            ->setProductId($product->getId());

        if ($item instanceof AbstractItem) {
            if ($this->taxConfig->priceIncludesTax()) {
                $subscriptionOption
                    ->setInitialFee($item->getBaseAwSarpInitialFeeInclTax())
                    ->setTrialPrice($item->getBaseAwSarpTrialPriceInclTax())
                    ->setRegularPrice($item->getBaseAwSarpRegularPriceInclTax());
            } else {
                $subscriptionOption
                    ->setInitialFee($item->getBaseAwSarpInitialFee())
                    ->setTrialPrice($item->getBaseAwSarpTrialPrice())
                    ->setRegularPrice($item->getBaseAwSarpRegularPrice());
            }
        } else {
            $subscriptionOption
                ->setInitialFee(0)
                ->setTrialPrice(0)
                ->setRegularPrice(0);
        }

        return $subscriptionOption;
    }
}
