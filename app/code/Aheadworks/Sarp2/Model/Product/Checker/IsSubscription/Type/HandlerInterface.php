<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface HandlerInterface
 * @package Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type
 */
interface HandlerInterface
{
    /**
     * Check if subscribe action available for product
     *
     * @param ProductInterface $product
     * @param bool $subscriptionOnly
     * @return bool
     */
    public function check($product, $subscriptionOnly = false);
}
