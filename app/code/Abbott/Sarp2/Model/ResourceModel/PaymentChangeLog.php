<?php


namespace Abbott\Sarp2\Model\ResourceModel;




use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class PaymentChangeLog
 * @package Abbott\Sarp2\Model\ResourceModel
 */
class PaymentChangeLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * PaymentChangeLog constructor.
     * @param Context $context
     * @param DateTime $dateTime
     * @param null $connectionName
     */
    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context, DateTime $dateTime, $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_profile_payment_change_log', 'entity_id');
    }

    /**
     * @param \Abbott\Sarp2\Model\PaymentChangeLog $object
     * @return PaymentChangeLog
     * @throws \Exception
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $date = $this->dateTime->gmtDate();
        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($date);
        }
        return parent::_beforeSave($object);
    }
}
