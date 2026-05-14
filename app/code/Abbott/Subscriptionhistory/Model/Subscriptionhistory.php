<?php
namespace Abbott\Subscriptionhistory\Model;

class Subscriptionhistory extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Abbott\Subscriptionhistory\Model\ResourceModel\Subscriptionhistory');
    }
}
