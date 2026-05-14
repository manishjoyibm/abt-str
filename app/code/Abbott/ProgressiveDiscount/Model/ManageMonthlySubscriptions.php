<?php

namespace Abbott\ProgressiveDiscount\Model;

use Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * ManageMonthlySubscription model
 */
class ManageMonthlySubscriptions extends AbstractModel implements ManageMonthlySubscriptionsInterface, IdentityInterface
{

    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;
    /**
    * No route InboundFeed Id
    */
    const NOROUTE_FEED_ID = 'no-route';

    /**
    * abbott_monthlySubscription cache tag
    */
    const CACHE_TAG = 'monthlySubscription';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events name
     * @var string
     */
    protected $_eventPrefix = 'monthly_subscriptions_collection';

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Abbott\ProgressiveDiscount\Model\ResourceModel\ManageMonthlySubscriptions::class);
    }

    /**
     * Load object data
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
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Load No-Route InboundFeed
     * @return SELF_CLASS
     */
    public function noRouteInboundFeed()
    {
        return $this->load(self::NOROUTE_ROW_ID, $this->getIdFieldName());
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get RowId
     * @return int|null
     */
    public function getRowId()
    {
        return parent::getData(self::ROW_ID);
    }
    /**
     * Get ProfileId
     * @return string|null
     */
    public function getProfileId()
    {
        return parent::getData(self::PROFILE_ID);
    }
    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }
    /**
     * Get UpdatedAt
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }
    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }
    /**
     * Get CurrentMonth
     * @return string|null
     */
    public function getCurrentMonth()
    {
        return parent::getData(self::CURRENT_MONTH);
    }
    /**
     * Get CustomerEmail
     * @return string|null
     */
    public function getCustomerEmail()
    {
        return parent::getData(self::CUSTOMER_EMAIL);
    }
    /**
     * Set RowId
     * @param int $rowId
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface
     */
    public function setRowId($rowId)
    {
        return parent::setData(self::ROW_ID, $rowId);
    }
    /**
     * Set ProfileId
     * @param string $profileId
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface
     */
    public function setProfileId($profileId)
    {
        return parent::setData(self::PROFILE_ID, $profileId);
    }
    /**
     * Set CreatedAt
     * @param string $createdAt
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface
     */
    public function setCreatedAt($createdAt)
    {
        return parent::setData(self::CREATED_AT, $createdAt);
    }
    /**
     * Set UpdatedAt
     * @param string $updatedAt
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return parent::setData(self::UPDATED_AT, $updatedAt);
    }
    /**
     * Set Status
     * @param string $status
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface
     */
    public function setStatus($status)
    {
        return parent::setData(self::STATUS, $status);
    }
    /**
     * Set CurrentMonth
     * @param string $currentMonth
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface
     */
    public function setCurrentMonth($currentMonth)
    {
        return parent::setData(self::CURRENT_MONTH, $currentMonth);
    }
    /**
     * Set Type
     * @param string $customerEmail
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface
     */
    public function setCustomerEmail($customerEmail)
    {
        return parent::setData(self::CUSTOMER_EMAIL, $customerEmail);
    }
}
