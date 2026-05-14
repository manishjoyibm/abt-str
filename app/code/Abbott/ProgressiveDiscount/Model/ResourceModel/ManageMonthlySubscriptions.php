<?php

namespace Abbott\ProgressiveDiscount\Model\ResourceModel;

use Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;

/**
 * ManageMonthlySubscriptions mysql resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManageMonthlySubscriptions extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * Constructor
     *
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
    protected function _construct()
    {
        $this->_init('manage_monthly_subscription', 'row_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(ManageMonthlySubscriptionsInterface::class)->getEntityConnection();
    }
}
