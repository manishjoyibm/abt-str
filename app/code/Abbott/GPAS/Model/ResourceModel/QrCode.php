<?php


namespace Abbott\GPAS\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class QrCode extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * Construct function
     *
     * QrCode constructor.
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
     *
     */
    protected function _construct()
    {
        $this->_init('abbott_gpas_qrcode', 'entity_id');
    }

    /**
     * Set date of last update
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /* @var $object \Abbott\GPAS\Model\QrCode */
        $date = $this->dateTime->gmtDate();
        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($date);
        } else {
            $object->setUpdatedAt($date);
        }
        return parent::_beforeSave($object);
    }
}
