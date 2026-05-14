<?php

namespace Abbott\Hartehanks\Plugin\Returns;

use Magento\Sales\Model\OrderRepository;

class Submit
{
    protected $orderRepository;

    public function __construct(
        OrderRepository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    public function afterExecute(\Magento\Rma\Controller\Returns\Submit $subject, $result)
    {
        $orderId = $subject->getRequest()->getParam('order_id', false);
        $order = $this->orderRepository->get($orderId);
        $order->setStatus('return');
        $order->save();
        return $result;
    }
}
