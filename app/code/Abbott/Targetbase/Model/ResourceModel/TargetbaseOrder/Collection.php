<?php

namespace Abbott\Targetbase\Model\ResourceModel\TargetbaseOrder;

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
            \Abbott\Targetbase\Model\TargetbaseOrder::class,
            \Abbott\Targetbase\Model\ResourceModel\TargetbaseOrder::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
