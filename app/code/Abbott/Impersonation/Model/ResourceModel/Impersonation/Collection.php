<?php

namespace Abbott\Impersonation\Model\ResourceModel\Impersonation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Abbott\Impersonation\Model\Impersonation::class, \Abbott\Impersonation\Model\ResourceModel\Impersonation::class);
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
