<?php

namespace Abbott\WorkdayFeed\Model\ResourceModel;

use Abbott\WorkdayFeed\Api\Data\InboundFeedInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;

/**
 * InboundFeed mysql resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InboundFeed extends AbstractDb
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
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
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
        $this->_init('apollo_inbound_feed', 'feed_id');
    }
    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(InboundFeedInterface::class)->getEntityConnection();
    }
}
