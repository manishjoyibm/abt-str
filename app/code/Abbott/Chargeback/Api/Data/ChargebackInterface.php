<?php

namespace Abbott\Chargeback\Api\Data;

interface ChargebackInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ORDER_ID = 'order_id';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    const CHARGEBACK_ID = 'chargeback_id';
    const CHARGEBACK_DATA = 'chargeback_data';
    const STATUS = 'status';
    const MESSAGE = 'message';

    /**
     * Get chargeback_id
     * @return string|null
     */
    public function getChargebackId();

    /**
     * Set chargeback_id
     * @param string $chargebackId
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setChargebackId($chargebackId);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setOrderId($orderId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\Chargeback\Api\Data\ChargebackExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Abbott\Chargeback\Api\Data\ChargebackExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Chargeback\Api\Data\ChargebackExtensionInterface $extensionAttributes
    );

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
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
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get chargeBackData
     * @return string|null
     */
    public function getChargeBackData();

    /**
     * Set chargeBackData
     * @param string $data
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setChargeBackData($data);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
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
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     */
    public function setMessage($message);
}
