<?php

namespace Abbott\Chargeback\Model\ResourceModel;

class Chargeback extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('abbott_chargeback_log', 'chargeback_id');
    }
}
