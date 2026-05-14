<?php

namespace Abbott\WorkdayFeed\Model;

use Abbott\WorkdayFeed\Api\Data\InboundFeedLogInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * InboundFeed model
 *
 * @method Block setStoreId(int $storeId)
 * @method int getStoreId()
 */
class InboundFeedLog extends AbstractModel implements InboundFeedLogInterface, IdentityInterface
{
    /**
     * No route InboundFeed Id
     */
    public const NOROUTE_FEED_ID = 'no-route';

    /**
     * abbott_workfeed cache tag
     */
    public const CACHE_TAG = 'inboundfeed';
    public const STATUS_ENABLED = 'Enabled';
    public const STATUS_DISABLED = 'Disabled';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events name
     * @var string
     */
    protected $_eventPrefix = 'abbott_workfeed';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\InboundFeedLog::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param null $field
     * @return SELF_CLASS|InboundFeedLog
     */
    public function load($id, $field = null): SELF_CLASS|static
    {
        if ($id === null) {
            return $this->noRouteInboundFeed();
        }
        return parent::load($id, $field);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Load No-Route InboundFeed
     *
     */
    public function noRouteInboundFeed(): InboundFeedLog|SELF_CLASS
    {
        return $this->load(self::NOROUTE_FEED_ID, $this->getIdFieldName());
    }

    /**
     * Method getAvailableStatuses
     *
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get RowId
     *
     * @return int|null
     */
    public function getRowId(): ?int
    {
        return parent::getData(self::ROW_ID);
    }
    /**
     * Get Upi
     *
     * @return int|null
     */
    public function getUpi(): ?int
    {
        return parent::getData(self::UPI);
    }
    /**
     * Get RecordStatus
     *
     * @return string|null
     */
    public function getRecordStatus(): ?string
    {
        return parent::getData(self::RECORD_STATUS);
    }
    /**
     * Get Record
     *
     * @return string|null
     */
    public function getRecord(): ?string
    {
        return parent::getData(self::RECORD);
    }
    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return parent::getData(self::STATUS);
    }
    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return parent::getData(self::MESSAGE);
    }
    /**
     * Get FeedId
     *
     * @return int|null
     */
    public function getFeedId(): ?int
    {
        return parent::getData(self::FEED_ID);
    }
    /**
     * Get created_at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return parent::getData(self::CREATED_AT);
    }
    /**
     * Get UpdatedAt
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set RowId
     *
     * @param string $rowId
     * @return InboundFeedLogInterface
     */
    public function setRowId(string $rowId): InboundFeedLogInterface
    {
        return parent::setData(self::ROW_ID, $rowId);
    }
    /**
     * Get Upi
     *
     * @param string $upi
     * @return InboundFeedLogInterface
     */
    public function setUpi(string $upi): InboundFeedLogInterface
    {
        return parent::setData(self::UPI, $upi);
    }
    /**
     * Get RecordStatus
     *
     * @param string $recordStatus
     * @return InboundFeedLogInterface
     */
    public function setRecordStatus(string $recordStatus): InboundFeedLogInterface
    {
        return parent::setData(self::RECORD_STATUS, $recordStatus);
    }
    /**
     * Set FileName
     *
     * @param string $record
     * @return InboundFeedLogInterface
     */
    public function setRecord(string $record): InboundFeedLogInterface
    {
        return parent::setData(self::RECORD, $record);
    }
    /**
     * Set Status
     *
     * @param string $status
     * @return InboundFeedLogInterface
     */
    public function setStatus(string $status): InboundFeedLogInterface
    {
        return parent::setData(self::STATUS, $status);
    }
    /**
     * Set Message
     *
     * @param string $message
     * @return InboundFeedLogInterface
     */
    public function setMessage(string $message): InboundFeedLogInterface
    {
        return parent::setData(self::MESSAGE, $message);
    }

    /**
     * Set FeedId
     *
     * @param int $feedId
     * @return \Abbott\WorkdayFeed\InboundFeedLogInterface
     */
    public function setFeedId(int $feedId): \Abbott\WorkdayFeed\InboundFeedLogInterface
    {
        return parent::setData(self::FEED_ID, $feedId);
    }
    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return InboundFeedLogInterface
     */
    public function setCreatedAt(string $createdAt): InboundFeedLogInterface
    {
        return parent::setData(self::CREATED_AT, $createdAt);
    }
    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return InboundFeedLogInterface
     */
    public function setUpdatedAt(string $updatedAt): InboundFeedLogInterface
    {
        return parent::setData(self::UPDATED_AT, $updatedAt);
    }
}
