<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface ResultInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Action
 */
interface ResultInterface
{
    /**
     * Get order
     *
     * @return OrderInterface
     */
    public function getOrder();
}
