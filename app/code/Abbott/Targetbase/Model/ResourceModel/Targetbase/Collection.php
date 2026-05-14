<?php

namespace Abbott\Targetbase\Model\ResourceModel\Targetbase;

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
            \Abbott\Targetbase\Model\Targetbase::class,
            \Abbott\Targetbase\Model\ResourceModel\Targetbase::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
