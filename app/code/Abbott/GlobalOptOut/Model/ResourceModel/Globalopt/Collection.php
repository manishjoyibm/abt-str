<?php

namespace Abbott\GlobalOptOut\Model\ResourceModel\Globalopt;

use Abbott\GlobalOptOut\Model\Globalopt;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            Globalopt::class,
            \Abbott\GlobalOptOut\Model\ResourceModel\Globalopt::class
        );
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
