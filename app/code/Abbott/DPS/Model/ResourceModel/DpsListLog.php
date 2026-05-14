<?php
namespace Abbott\DPS\Model\ResourceModel;

use Exception;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DpsListLog extends AbstractDb
{

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * DpsListLog constructor.
     * @param Context $context
     * @param DateTime $dateTime
     * @param mixed|null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        mixed $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime;
    }

    /**
     * Resource Initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('abbott_dps_list_log', 'entity_id');
    }

    /**
     * Perform action before object save
     *
     * @param AbstractModel $object
     * @return DpsListLog
     * @throws Exception
     */
    protected function _beforeSave(AbstractModel $object): DpsListLog
    {
        $date = $this->dateTime->gmtDate();
        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($date);
        }
        return parent::_beforeSave($object);
    }
}
