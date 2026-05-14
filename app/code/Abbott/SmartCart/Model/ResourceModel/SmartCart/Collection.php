<?php


namespace Abbott\SmartCart\Model\ResourceModel\SmartCart;

use Abbott\SmartCart\Model\SmartCart;
use Abbott\SmartCart\Model\ResourceModel\SmartCart as SmartCartResource;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'abbott_smartcart_smartcart_collection';
    protected $_eventObject = 'abbott_smartcart_smartcart_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SmartCart::class, SmartCartResource::class);
    }
}
