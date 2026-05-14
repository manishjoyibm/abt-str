<?php


namespace Abbott\Subscriptionhistory\Model\ResourceModel\Subscriptionhistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Abbott\Subscriptionhistory\Model\Subscriptionhistory',
            'Abbott\Subscriptionhistory\Model\ResourceModel\Subscriptionhistory'
        );
    }
}
