<?php

namespace Abbott\Hartehanks\Model\ResourceModel\HhFindorder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'hhfindorder_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\Hartehanks\Model\HhFindorder::class,
            \Abbott\Hartehanks\Model\ResourceModel\HhFindorder::class
        );
    }
}
