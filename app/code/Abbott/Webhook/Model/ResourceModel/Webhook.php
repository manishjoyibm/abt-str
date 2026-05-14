<?php

namespace Abbott\Webhook\Model\ResourceModel;

class Webhook extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('apollo_webhook', 'webhook_id');
    }
}
