<?php

namespace Abbott\Webhook\Model\ResourceModel\Webhook;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'webhook_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\Webhook\Model\Webhook::class,
            \Abbott\Webhook\Model\ResourceModel\Webhook::class
        );
    }
}
