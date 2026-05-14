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
class Result implements ResultInterface
{
    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @param OrderInterface $order
     */
    public function __construct(OrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->order;
    }
}
