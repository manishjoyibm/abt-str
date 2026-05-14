<?php


namespace Abbott\GPAS\Model\ResourceModel\QrCode;

use Abbott\GPAS\Model\QrCode;
use Abbott\GPAS\Model\ResourceModel\QrCode as QrCodeResource;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'abbott_gpas_qrcode_collection';
    protected $_eventObject = 'abbott_gpas_qrcode_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QrCode::class, QrCodeResource::class);
    }
}
