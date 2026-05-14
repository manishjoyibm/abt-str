<?php

namespace Abbott\Hartehanks\Model\ResourceModel;

class HarteHank extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('apollo_hartehank_log', 'hartehank_id');
    }
}
