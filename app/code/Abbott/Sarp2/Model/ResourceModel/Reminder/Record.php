<?php
namespace Abbott\Sarp2\Model\ResourceModel\Reminder;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Record extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('abbott_sarp2_annual_reminder', 'entity_id');
    }
}