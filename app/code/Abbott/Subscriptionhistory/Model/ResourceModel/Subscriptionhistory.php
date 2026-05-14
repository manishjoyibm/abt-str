<?php
namespace Abbott\Subscriptionhistory\Model\ResourceModel;

class Subscriptionhistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('aw_sarp2_subscription_history', 'entity_id');
    }
}
