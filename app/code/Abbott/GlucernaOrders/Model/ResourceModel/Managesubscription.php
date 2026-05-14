<?php

namespace Abbott\GlucernaOrders\Model\ResourceModel;

class Managesubscription extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('apollo_glucerna_subscription_plans', 'managesubscription_id');
    }
}
