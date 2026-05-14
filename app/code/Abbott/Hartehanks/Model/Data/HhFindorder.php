<?php

namespace Abbott\Hartehanks\Model\Data;

use Abbott\Hartehanks\Api\Data\HhFindorderInterface;

class HhFindorder extends \Magento\Framework\Api\AbstractExtensibleObject implements HhFindorderInterface
{
    /**
     * Get hhfindorder_id
     * @return string|null
     */
    public function getHhfindorderId()
    {
        return $this->_get(self::HHFINDORDER_ID);
    }

    /**
     * Set hhfindorder_id
     * @param string $hhfindorderId
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setHhfindorderId($hhfindorderId)
    {
        return $this->setData(self::HHFINDORDER_ID, $hhfindorderId);
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
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Abbott\Hartehanks\Api\Data\HhFindorderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Hartehanks\Api\Data\HhFindorderExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
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
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get first_order_date
     * @return string|null
     */
    public function getFirstOrderDate()
    {
        return $this->_get(self::FIRST_ORDER_DATE);
    }

    /**
     * Set first_order_date
     * @param string $firstOrderDate
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setFirstOrderDate($firstOrderDate)
    {
        return $this->setData(self::FIRST_ORDER_DATE, $firstOrderDate);
    }

    /**
     * Get last_order_date
     * @return string|null
     */
    public function getLastOrderDate()
    {
        return $this->_get(self::LAST_ORDER_DATE);
    }

    /**
     * Set last_order_date
     * @param string $lastOrderDate
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setLastOrderDate($lastOrderDate)
    {
        return $this->setData(self::LAST_ORDER_DATE, $lastOrderDate);
    }
}
