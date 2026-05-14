<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;

/**
 * Interface ProviderInterface
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Details\Config
 */
interface ProviderInterface
{
    /**
     * Get subscription details config for product
     *
     * @param int $productId
     * @param string $productTypeId
     * @param ProfileItemInterface|null $item
     * @return array
     */
    public function getConfig($productId, $productTypeId, $item = null);

    /**
     * Get subscription details config
     *
     * @param int $productId
     * @param ProfileItemInterface|null $item
     * @param ProfileInterface $profile
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSubscriptionDetailsConfig($productId, $item = null, $profile = null);
}
