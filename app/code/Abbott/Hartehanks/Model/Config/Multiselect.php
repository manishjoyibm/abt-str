<?php

namespace Abbott\Hartehanks\Model\Config;

class Multiselect implements \Magento\Framework\Option\ArrayInterface
{
    protected $orderStatus;

    public function __construct(
        \Magento\Sales\Model\Order\Status $orderStatus
    ) {
        $this->orderStatus = $orderStatus;
    }

    public function getOrderStatuses()
    {
        $orderStatuses = $this->orderStatus->getCollection();
        $orderStatuses->joinStates();
        return $orderStatuses;
    }

    public function toOptionArray()
    {
        $res = [];
        foreach ($this->getOrderStatuses()->getData() as $value) {
            $res[] = ['value' => $value['status'], 'label' => $value['label'].' ('.$value['state'].')'];
        }
        return $res;
    }
}
