<?php

namespace Abbott\Hartehanks\Model\Data;

use Abbott\Hartehanks\Api\Data\HarteHankInterface;

class HarteHank extends \Magento\Framework\Api\AbstractExtensibleObject implements HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     */
    public function setHartehankId($hartehankId)
    {
        return $this->setData(self::HARTEHANK_ID, $hartehankId);
    }

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * Set product_id
     * @param string $productId
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\Hartehanks\Api\Data\HarteHankExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Abbott\Hartehanks\Api\Data\HarteHankExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Hartehanks\Api\Data\HarteHankExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get qty_available
     * @return string|null
     */
    public function getQtyAvailable()
    {
        return $this->_get(self::QTY_AVAILABLE);
    }

    /**
     * Set qty_available
     * @param string $qtyAvailable
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     */
    public function setQtyAvailable($qtyAvailable)
    {
        return $this->setData(self::QTY_AVAILABLE, $qtyAvailable);
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
