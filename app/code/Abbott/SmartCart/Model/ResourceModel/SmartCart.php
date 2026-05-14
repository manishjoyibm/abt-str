<?php

namespace Abbott\SmartCart\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class SmartCart extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * QrCode constructor.
     *
     * @param Context $context
     * @param DateTime $dateTime
     */
    public function __construct(
        Context $context,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->dateTime = $dateTime;
    }

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('abbott_smartcart_smartcart', 'entity_id');
    }

    /**
     * Set date of last update
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /* @var $object \Abbott\SmartCart\Model\SmartCart */
        $date = $this->dateTime->gmtDate();
        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($date);
        } else {
            $object->setUpdatedAt($date);
        }
        return parent::_beforeSave($object);
    }
}
