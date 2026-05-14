<?php
namespace Abbott\Sarp2\Model\Reminder;

use Magento\Framework\Model\AbstractModel;

class Record extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Abbott\Sarp2\Model\ResourceModel\Reminder\Record::class);
    }
}