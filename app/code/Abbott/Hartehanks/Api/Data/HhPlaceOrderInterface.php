<?php

namespace Abbott\Hartehanks\Api\Data;

interface HhPlaceOrderInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const HARTEHANK_ID = 'hartehank_id';
    const HH_REQ_ID = 'hh_req_id';
    const HH_DATA = 'hh_data';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    const WEBSITE = 'website';
    const ORDER_ID = 'order_id';
    const STATUS = 'status';
    const MESSAGE = 'message';

    /**
     * Get hartehank_id
     * @return string|null
     */
    public function getHartehankId();

    /**
     * Set hartehank_id
     * @param string $hartehankId
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setHartehankId($hartehankId);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setOrderId($orderId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Abbott\Hartehanks\Api\Data\HhPlaceOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Hartehanks\Api\Data\HhPlaceOrderExtensionInterface $extensionAttributes
    );

    /**
     * Get website
     * @return string|null
     */
    public function getWebsite();

    /**
     * Set website
     * @param string $website
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setWebsite($website);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setStatus($status);

    /**
     * Get message
     * @return string|null
     */
    public function getMessage();

    /**
     * Set message
     * @param string $message
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setMessage($message);

    /**
     * Get hh_data
     * @return string|null
     */
    public function getHhData();

    /**
     * Set hh_data
     * @param string $hhData
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setHhData($hhData);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get hh_req_id
     * @return string|null
     */
    public function getHhReqId();

    /**
     * Set hh_req_id
     * @param string $hhReqId
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     */
    public function setHhReqId($hhReqId);
}
