<?php

namespace Abbott\Hartehanks\Plugin\Rma;

use Magento\Sales\Model\OrderRepository;

class Save
{
    protected $orderRepository;

    public function __construct(
        OrderRepository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    public function afterExecute(\Magento\Rma\Controller\Adminhtml\Rma\Save $subject, $result)
    {
        $orderId = $subject->getRequest()->getParam('order_id');
        if ($orderId) {
            $orderEntity = $this->orderRepository->get($orderId);
            $orderEntity->setStatus('return');
            $orderEntity->save();
            return $result;
        }
    }
}
