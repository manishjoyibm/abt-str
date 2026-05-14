<?php
namespace Abbott\Sarp2\Plugin\Model\Order;


class AddressPlugin
{
    public function beforeSetTelephone(\Magento\Sales\Model\Order\Address $subject, $telephone)
    {
        $telephone = $telephone ?? "";
        return [$telephone];
    }
} 