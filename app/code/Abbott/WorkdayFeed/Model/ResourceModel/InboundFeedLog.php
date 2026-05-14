<?php

namespace Abbott\WorkdayFeed\Model\ResourceModel;

use Abbott\WorkdayFeed\Api\Data\InboundFeedLogInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;

/**
 * InboundFeed mysql resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InboundFeedLog extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected DateTime $dateTime;
    /**
     * @var EntityManager
     */
    protected EntityManager $entityManager;
    /**
     * @var MetadataPool
     */
    protected MetadataPool $metadataPool;
    /**
     * @param Context $context
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        string $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('apollo_inbound_workday_idx', 'row_id');
    }
    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(InboundFeedLogInterface::class)->getEntityConnection();
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     * @throws LocalizedException
     */
    public function load(AbstractModel $object, $value, $field = null): static
    {
        $object->beforeLoad($value, $field);
        if ($field === null) {
            $field = $this->getIdFieldName();
        }

        $connection = $this->getConnection();
        if ($connection && $value !== null) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);
        $object->afterLoad();
        $object->setOrigData();
        $object->setHasDataChanges(false);

        return $this;
    }
}
