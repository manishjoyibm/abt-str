<?php

namespace Abbott\Hartehanks\Model\Data;

use Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface;

class HhPlaceOrder extends \Magento\Framework\Api\AbstractExtensibleObject implements HhPlaceOrderInterface
{
    /**
     * Get hartehank_id
     * @return string|null
     */
    public function getHartehankId()
    {
        return $this->_get(self::HARTEHANK_ID);
    }

    /**
     * Set hartehank_id
     * @param string $hartehankId
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setHartehankId($hartehankId)
    {
        return $this->setData(self::HARTEHANK_ID, $hartehankId);
    }

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param string $orderId
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Abbott\Hartehanks\Api\Data\HhPlaceOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Hartehanks\Api\Data\HhPlaceOrderExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get website
     * @return string|null
     */
    public function getWebsite()
    {
        return $this->_get(self::WEBSITE);
    }

    /**
     * Set website
     * @param string $website
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setWebsite($website)
    {
        return $this->setData(self::WEBSITE, $website);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get message
     * @return string|null
     */
    public function getMessage()
    {
        return $this->_get(self::MESSAGE);
    }

    /**
     * Set message
     * @param string $message
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Get hh_data
     * @return string|null
     */
    public function getHhData()
    {
        return $this->_get(self::HH_DATA);
    }

    /**
     * Set hh_data
     * @param string $hhData
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setHhData($hhData)
    {
        return $this->setData(self::HH_DATA, $hhData);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get hh_req_id
     * @return string|null
     */
    public function getHhReqId()
    {
        return $this->_get(self::HH_REQ_ID);
    }

    /**
     * Set hh_req_id
     * @param string $hhReqId
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setHhReqId($hhReqId)
    {
        return $this->setData(self::HH_REQ_ID, $hhReqId);
    }
}
