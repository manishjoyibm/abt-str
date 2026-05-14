<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerResolver;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\Order\InventoryManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class PlaceOrder
 * @package Aheadworks\Sarp2\Engine\Payment\Action
 */
class PlaceOrder
{
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var InventoryManagement
     */
    private $inventoryManagement;

    /**
     * @var DataResolver
     */
    private $setDataResolver;

    /**
     * @var HandlerResolver
     */
    private $exceptionHandlerResolver;

    /**
     * @var ProfileInterface[]
     */
    private $processed = [];

    /**
     * @param OrderManagementInterface $orderManagement
     * @param InventoryManagement $inventoryManagement
     * @param DataResolver $setDataResolver
     * @param HandlerResolver $exceptionHandlerResolver
     */
    public function __construct(
        OrderManagementInterface $orderManagement,
        InventoryManagement $inventoryManagement,
        DataResolver $setDataResolver,
        HandlerResolver $exceptionHandlerResolver
    ) {
        $this->orderManagement = $orderManagement;
        $this->inventoryManagement = $inventoryManagement;
        $this->setDataResolver = $setDataResolver;
        $this->exceptionHandlerResolver = $exceptionHandlerResolver;
    }

    /**
     * Place order
     *
     * @param OrderInterface $order
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return OrderInterface
     * @throws \Exception
     */
    public function place($order, $paymentsInfo)
    {
        $this->processed = [];
        try {
            array_walk($paymentsInfo, [$this, 'subtractStockQty']);
            $this->orderManagement->place($order);
        } catch (\Exception $e) {
            array_walk($this->processed, [$this, 'revertStockQty']);

            $handler = $this->exceptionHandlerResolver->getHandler(
                $e,
                $this->setDataResolver->getPaymentMethod($paymentsInfo)
            );
            $exception = $handler ? $handler->handle($e) : $e;
            throw $exception;
        }
        return $order;
    }

    /**
     * Subtract profile items quantities from stock
     *
     * @param PaymentInfoInterface $paymentInfo
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function subtractStockQty($paymentInfo)
    {
        $profile = $paymentInfo->getProfile();
        $this->inventoryManagement->subtract($profile);
        $this->processed[] = $profile;
    }

    /**
     * Revert profile items quantities
     *
     * @param ProfileInterface $profile
     * @return void
     */
    private function revertStockQty($profile)
    {
        $this->inventoryManagement->revert($profile);
    }
}
