<?php

namespace Abbott\Hartehanks\Api\Data;

interface HhFindorderInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const CREATED_AT        = 'created_at';
    const HHFINDORDER_ID    = 'hhfindorder_id';
    const UPDATED_AT        = 'updated_at';
    const LAST_ORDER_DATE   = 'last_order_date';
    const FIRST_ORDER_DATE  = 'first_order_date';

    /**
     * Get hhfindorder_id
     * @return string|null
     */
    public function getHhfindorderId();

    /**
     * Set hhfindorder_id
     * @param string $hhfindorderId
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setHhfindorderId($hhfindorderId);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Abbott\Hartehanks\Api\Data\HhFindorderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Hartehanks\Api\Data\HhFindorderExtensionInterface $extensionAttributes
    );

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get first_order_date
     * @return string|null
     */
    public function getFirstOrderDate();

    /**
     * Set first_order_date
     * @param string $firstOrderDate
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setFirstOrderDate($firstOrderDate);

    /**
     * Get last_order_date
     * @return string|null
     */
    public function getLastOrderDate();

    /**
     * Set last_order_date
     * @param string $lastOrderDate
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     */
    public function setLastOrderDate($lastOrderDate);
}
