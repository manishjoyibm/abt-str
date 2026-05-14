<?php

namespace Abbott\Webhook\Model\Data;

use Abbott\Webhook\Api\Data\WebhookExtensionInterface;
use Abbott\Webhook\Api\Data\WebhookInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;

class Webhook extends \Magento\Framework\Api\AbstractExtensibleObject implements WebhookInterface
{
    /**
     * Get webhook_id
     *
     * @return string|null
     */
    public function getWebhookId()
    {
        return $this->_get(self::WEBHOOK_ID);
    }

    /**
     * Set webhook_id
     *
     * @param string $webhookId
     * @return WebhookInterface
     */
    public function setWebhookId($webhookId)
    {
        return $this->setData(self::WEBHOOK_ID, $webhookId);
    }

    /**
     * Get event_name
     *
     * @return string|null
     */
    public function getEventName()
    {
        return $this->_get(self::EVENT_NAME);
    }

    /**
     * Set event_name
     *
     * @param string $eventName
     * @return WebhookInterface
     */
    public function setEventName($eventName)
    {
        return $this->setData(self::EVENT_NAME, $eventName);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param WebhookExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        WebhookExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get path
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->_get(self::PATH);
    }

    /**
     * Set path
     *
     * @param string $path
     * @return WebhookInterface
     */
    public function setPath($path)
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * Get created_at
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     *
     * @param string $createdAt
     * @return WebhookInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     *
     * @param string $updatedAt
     * @return WebhookInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
