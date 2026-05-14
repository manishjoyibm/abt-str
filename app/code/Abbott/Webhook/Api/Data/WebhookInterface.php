<?php


namespace Abbott\Webhook\Api\Data;

interface WebhookInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    public const UPDATED_AT = 'updated_at';
    public const WEBHOOK_ID = 'webhook_id';
    public const CREATED_AT = 'created_at';
    public const EVENT_NAME = 'event_name';
    public const PATH = 'path';

    /**
     * Get webhook_id
     *
     * @return string|null
     */
    public function getWebhookId();

    /**
     * Set webhook_id
     *
     * @param string $webhookId
     * @return \Abbott\Webhook\Api\Data\WebhookInterface
     */
    public function setWebhookId($webhookId);

    /**
     * Get event_name
     *
     * @return string|null
     */
    public function getEventName();

    /**
     * Set event_name
     *
     * @param string $eventName
     * @return \Abbott\Webhook\Api\Data\WebhookInterface
     */
    public function setEventName($eventName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Abbott\Webhook\Api\Data\WebhookExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Abbott\Webhook\Api\Data\WebhookExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\Webhook\Api\Data\WebhookExtensionInterface $extensionAttributes
    );

    /**
     * Get path
     *
     * @return string|null
     */
    public function getPath();

    /**
     * Set path
     *
     * @param string $path
     * @return \Abbott\Webhook\Api\Data\WebhookInterface
     */
    public function setPath($path);

    /**
     * Get created_at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     *
     * @param string $createdAt
     * @return \Abbott\Webhook\Api\Data\WebhookInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     *
     * @param string $updatedAt
     * @return \Abbott\Webhook\Api\Data\WebhookInterface
     */
    public function setUpdatedAt($updatedAt);
}
