<?php

namespace Abbott\GigyaIM\Api\Data;

interface SsmCartInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const ID = 'id';
    public const EMAIL = 'email';
    public const WEBSITE_ID = 'website_id';
    public const MASKED_CART_ID = 'masked_cart_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get id
     *
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get masked cart id
     *
     * @return string|null
     */
    public function getMaskedCartId();

    /**
     * Set masked cart id
     *
     * @param string $maskedCartId
     * @return $this
     */
    public function setMaskedCartId($maskedCartId);

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Abbott\GigyaIM\Api\Data\SsmCartExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Abbott\GigyaIM\Api\Data\SsmCartExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(SsmCartExtensionInterface $extensionAttributes);
}
