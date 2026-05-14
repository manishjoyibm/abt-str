<?php

namespace Abbott\Chargeback\Model\Data;

use Abbott\Chargeback\Api\Data\ChargebackInterface;

class Chargeback extends \Magento\Framework\Api\AbstractExtensibleObject implements ChargebackInterface
{

    /**
     * Get chargeback_id
     * @return string|null
     */
    public function getChargebackId()
    {
        return $this->_get(self::CHARGEBACK_ID);
    }

    /**
     * Set chargeback_id
     * @param string $chargebackId
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setChargebackId($chargebackId)
    {
        return $this->setData(self::CHARGEBACK_ID, $chargebackId);
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
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\Chargeback\Api\Data\ChargebackExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Abbott\Chargeback\Api\Data\ChargebackExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Chargeback\Api\Data\ChargebackExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
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
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
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
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get data
     * @return string|null
     */
    public function getChargeBackData()
    {
        return $this->_get(self::CHARGEBACK_DATA);
    }

    /**
     * Set data
     * @param string $data
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setChargeBackData($data)
    {
        return $this->setData(self::CHARGEBACK_DATA, $data);
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
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
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
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }
}
