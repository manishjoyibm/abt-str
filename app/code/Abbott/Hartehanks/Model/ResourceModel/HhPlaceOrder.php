<?php

namespace Abbott\Hartehanks\Model\ResourceModel;

class HhPlaceOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('apollo_hartehank_placeorder_log', 'hartehank_id');
    }
}
