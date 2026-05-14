<?php


namespace Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog;


use Abbott\Sarp2\Model\PaymentChangeLog;
use Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog as PaymentChangeLogResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PaymentChangeLog::class, PaymentChangeLogResource::class);
    }
}
