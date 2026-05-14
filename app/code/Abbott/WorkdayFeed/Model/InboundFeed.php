<?php

namespace Abbott\WorkdayFeed\Model;

use Abbott\WorkdayFeed\Api\Data\InboundFeedInterface;
use Exception;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * InboundFeed model
 *
 * @method Block setStoreId(int $storeId)
 * @method int getStoreId()
 */
class InboundFeed extends AbstractModel implements InboundFeedInterface, IdentityInterface
{

    /**
     * InboundFeed's Statuses
     */
    public const STATUS_ENABLED = 1;

    public const STATUS_DISABLED = 0;
    /**
     * No route InboundFeed Id
     **/
    public const NOROUTE_FEED_ID = 'no-route';

    /**
     * abbott_workfeed cache tag
     */
    public const CACHE_TAG = 'inboundfeed';

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
        $this->_init(ResourceModel\InboundFeed::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
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
     * @return SELF_CLASS
     */
    public function noRouteInboundFeed()
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
     * Get FeedId
     *
     * @return int|null
     */
    public function getFeedId(): ?int
    {
        return parent::getData(self::FEED_ID);
    }
    /**
     * Get FileName
     *
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return parent::getData(self::FILE_NAME);
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
     * Get Type
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return parent::getData(self::TYPE);
    }

    /**
     * Set FeedId
     *
     * @param int $feedId
     * @return \Abbott\WorkdayFeed\InboundFeedInterface
     */
    public function setFeedId(int $feedId): \Abbott\WorkdayFeed\InboundFeedInterface
    {
        return parent::setData(self::FEED_ID, $feedId);
    }
    /**
     * Set FileName
     *
     * @param string $fileName
     * @return InboundFeedInterface
     */
    public function setFileName(string $fileName): InboundFeedInterface
    {
        return parent::setData(self::FILE_NAME, $fileName);
    }
    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return InboundFeedInterface
     */
    public function setCreatedAt(string $createdAt): InboundFeedInterface
    {
        return parent::setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return InboundFeedInterface
     */
    public function setUpdatedAt(string $updatedAt): InboundFeedInterface
    {
        return parent::setData(self::UPDATED_AT, $updatedAt);
    }
    /**
     * Set Status
     *
     * @param string $status
     * @return InboundFeedInterface
     */
    public function setStatus(string $status): InboundFeedInterface
    {
        return parent::setData(self::STATUS, $status);
    }
    /**
     * Set Message
     *
     * @param string $message
     * @return InboundFeedInterface
     */
    public function setMessage(string $message): InboundFeedInterface
    {
        return parent::setData(self::MESSAGE, $message);
    }
    /**
     * Set Type
     *
     * @param string $type
     * @return InboundFeedInterface
     */
    public function setType(string $type): InboundFeedInterface
    {
        return parent::setData(self::TYPE, $type);
    }

    /**
     * Method submitReport
     *
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function submitReport(array $data): static
    {
        $this->setType($data[0]);
        $this->setFileName($data[1]);
        $this->setStatus($data[2]);
        $this->setMessage($data[3]);
        $this->save();
        return $this;
    }

    /**
     * Method updateReport
     *
     * @param int|null $id
     * @param string $status
     * @param string $message
     * @return void
     * @throws Exception
     */
    public function updateReport(?int $id, string $status, string $message): void
    {
        $entity = $this->load($id);
        $entity->setStatus($status);
        $entity->setMessage($message);
        $entity->save();
    }
}
