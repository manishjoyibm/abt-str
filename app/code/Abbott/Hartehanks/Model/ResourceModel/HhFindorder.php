<?php

namespace Abbott\Hartehanks\Model\ResourceModel;

class HhFindorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('apollo_hartehank_findorder_log', 'hhfindorder_id');
    }
}
