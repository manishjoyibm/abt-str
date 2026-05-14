<?php


namespace Abbott\Sarp2\Model;


use Abbott\Sarp2\Api\Data\PaymentChangeLogInterface;
use Magento\Framework\Model\AbstractModel;
use Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog as ResourceModel;

/**
 * Class PaymentChangeLog
 * @package Abbott\Sarp2\Model
 */
class PaymentChangeLog extends AbstractModel implements PaymentChangeLogInterface
{
    /**
     *
     */
    const ENTITY = 'profile_payment_change_log';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getHasFailed()
    {
        return $this->getData(self::HAS_FAILED);
    }

    /**
     * @inheritDoc
     */
    public function setHasFailed($failed)
    {
        return $this->setData(self::HAS_FAILED, $failed);
    }


}
