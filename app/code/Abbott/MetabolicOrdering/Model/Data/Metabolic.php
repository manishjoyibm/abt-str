<?php

namespace Abbott\MetabolicOrdering\Model\Data;

use Abbott\MetabolicOrdering\Api\Data\MetabolicInterface;
use Abbott\MetabolicOrdering\Api\Data\MetabolicOrderingExtensionInterface;

class Metabolic extends \Magento\Framework\Model\AbstractExtensibleModel implements MetabolicInterface
{
    /**
     * Get entityId
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }
    /**
     * Get customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set CustomerId
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get customer_email
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->_get(self::CUSTOMER_EMAIL);
    }

    /**
     * Set CustomerEmail
     *
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Get adminuser
     *
     * @return string
     */
    public function getAdminUser()
    {
        return $this->_get(self::ADMIN_USER);
    }

    /**
     * Set admin user
     *
     * @param string $adminUser
     * @return $this
     */
    public function setAdminUser($adminUser)
    {
        return $this->setData(self::ADMIN_USER, $adminUser);
    }

    /**
     * Get qty
     *
     * @return string
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * Set qty
     *
     * @param string $qty
     * @return $this
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Get qty
     *
     * @return string
     */
    public function getEnableEmail()
    {
        return $this->_get(self::ENABLE_EMAIL);
    }

    /**
     * Set EnableEmail
     *
     * @param int $enableEmail
     * @return $this
     */
    public function setEnableEmail($enableEmail)
    {
        return $this->setData(self::ENABLE_EMAIL, $enableEmail);
    }

    /**
     * Get ExpiryDate
     *
     * @return string
     */
    public function getExpiryDate()
    {
        return $this->_get(self::EXPIRY_DATE);
    }

    /**
     *  Set expiry_data
     *
     * @param string $expiryDate
     * @return $this
     */
    public function setExpiryDate($expiryDate)
    {
        return $this->setData(self::EXPIRY_DATE, $expiryDate);
    }

    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get UpdatedAt
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updatedAt
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return MetabolicOrderingExtensionInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param MetabolicOrderingExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(MetabolicOrderingExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
