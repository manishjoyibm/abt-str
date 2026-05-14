<?php

namespace Abbott\DPS\Model\ResourceModel\DpsListItemAddress;

use Abbott\DPS\Model\DpsListItemAddress;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'abbott_dps_list_address_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'abbott_dps_list_address_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            DpsListItemAddress::class,
            \Abbott\DPS\Model\ResourceModel\DpsListItemAddress::class
        );
    }
}
