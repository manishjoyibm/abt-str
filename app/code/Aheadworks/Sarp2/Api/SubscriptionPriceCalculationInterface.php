<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Api;

/**
 * Interface SubscriptionPriceCalculationInterface
 * @package Aheadworks\Sarp2\Api
 */
interface SubscriptionPriceCalculationInterface
{
    /**
     * Get automatic calculated trial product price for specified plan
     *
     * @param int $productId
     * @param int $planId
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAutoTrialPrice($productId, $planId);

    /**
     * Get automatic calculated trial product regular for specified plan
     *
     * @param int $productId
     * @param int $planId
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAutoRegularPrice($productId, $planId);
}
