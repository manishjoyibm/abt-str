<?php
namespace Abbott\Sarp2\Model\ResourceModel\Reminder\Record;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Abbott\Sarp2\Model\Reminder\Record as Model;
use Abbott\Sarp2\Model\ResourceModel\Reminder\Record as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}