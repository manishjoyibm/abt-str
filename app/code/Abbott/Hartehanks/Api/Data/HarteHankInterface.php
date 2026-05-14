<?php

namespace Abbott\Hartehanks\Api\Data;

interface HarteHankInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const PRODUCT_ID      = 'product_id';
    const HH_DATA         = 'hh_data';
    const UPDATED_AT      = 'updated_at';
    const QTY_AVAILABLE   = 'qty_available';
    const CREATED_AT      = 'created_at';
    const STATUS          = 'status';
    const MESSAGE         = 'message';
    const HARTEHANK_ID    = 'hartehank_id';

    /**
     * Get hartehank_id
     * @return string|null
     */
    public function getHartehankId();

    /**
     * Set hartehank_id
     * @param string $hartehankId
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     */
    public function setHartehankId($hartehankId);

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     */
    public function setProductId($productId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Abbott\Hartehanks\Api\Data\HarteHankExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Abbott\Hartehanks\Api\Data\HarteHankExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Hartehanks\Api\Data\HarteHankExtensionInterface $extensionAttributes
    );

    /**
     * Get qty_available
     * @return string|null
     */
    public function getQtyAvailable();

    /**
     * Set qty_available
     * @param string $qtyAvailable
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     */
    public function setQtyAvailable($qtyAvailable);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
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
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     */
    public function setUpdatedAt($updatedAt);
}
