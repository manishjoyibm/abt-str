<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action\Type;

use Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\ActionInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoFactory;
use Aheadworks\Sarp2\Model\Profile\ToOrder;

/**
 * Class Single
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Type
 */
class Single implements ActionInterface
{
    /**
     * @var ToOrder
     */
    private $converter;

    /**
     * @var PlaceOrder
     */
    private $placeOrderService;

    /**
     * @var PaymentInfoFactory
     */
    private $paymentInfoFactory;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param ToOrder $converter
     * @param PlaceOrder $placeOrderService
     * @param PaymentInfoFactory $paymentInfoFactory
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        ToOrder $converter,
        PlaceOrder $placeOrderService,
        PaymentInfoFactory $paymentInfoFactory,
        ResultFactory $resultFactory
    ) {
        $this->converter = $converter;
        $this->placeOrderService = $placeOrderService;
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->resultFactory = $resultFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function pay(PaymentInterface $payment)
    {
        $profile = $payment->getProfile();
        $paymentPeriod = $payment->getPaymentPeriod();
        $order = $this->placeOrderService->place(
            $this->converter->convert($profile, $paymentPeriod),
            [
                $this->paymentInfoFactory->create(
                    [
                        'profile' => $profile,
                        'paymentPeriod' => $paymentPeriod
                    ]
                )
            ]
        );
        $profile
            ->setOrder($order)
            ->setLastOrderId($order->getEntityId())
            ->setLastOrderDate($order->getCreatedAt());
        return $this->resultFactory->create(['order' => $order]);
    }
}
