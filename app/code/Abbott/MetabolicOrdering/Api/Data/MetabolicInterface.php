<?php

namespace Abbott\MetabolicOrdering\Api\Data;

interface MetabolicInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const ENTITY_ID          ='entity_id';
    public const CUSTOMER_ID        ='customer_id';
    public const CREATED_AT         ='created_at';
    public const UPDATED_AT         ='updated_at';
    public const CUSTOMER_EMAIL     ='customer_email';
    public const SKU                = 'sku';
    public const ADMIN_USER         ='admin_user';
    public const QTY                ='qty';
    public const ENABLE_EMAIL       ='enable_email';
    public const EXPIRY_DATE        ='expiry_date';
    /**
     * Get EntityId
     *
     * @return int|null
     */
    public function getEntityId();
    /**
     * Get CustomerEmail
     *
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Get CreatedAt
     *
     * @return string|null
     */
    public function getCreatedAt();
    /**
     * Get UpdatedAt
     *
     * @return string|null
     */
    public function getUpdatedAt();
    /**
     * Get ExpiryDate
     *
     * @return string|null
     */
    public function getExpiryDate();
    /**
     * Get qty
     *
     * @return int|null
     */
    public function getQty();

    /**
     * Get EnableEmail
     *
     * @return int
     */
    public function getEnableEmail();

    /**
     * Get CustomerId
     *
     * @return int|null
     */
    public function getCustomerId();
    /**
     * Get AdminUser
     *
     * @return string|null
     */
    public function getAdminUser();
    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);
    /**
     * Set CustomerId
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);
    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
    /**
     * Set AdminUser
     *
     * @param string $adminUser
     * @return $this
     */
    public function setAdminUser($adminUser);
    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
    /**
     * Set Qty
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);
    /**
     * Set EnableEmail
     *
     * @param int $enableEmail
     * @return $this
     */
    public function setEnableEmail($enableEmail);
    /**
     * Set CustomerEmail
     *
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail);
    /**
     * Set ExpiryDate
     *
     * @param string $expiryDate
     * @return $this
     */
    public function setExpiryDate($expiryDate);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Abbott\MetabolicOrdering\Api\Data\MetabolicExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param MetabolicOrderingExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        MetabolicOrderingExtensionInterface $extensionAttributes
    );
}
