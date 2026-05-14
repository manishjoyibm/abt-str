<?php


namespace Abbott\Webhook\Api\Data;

interface WebhookSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get webhook list.
     *
     * @return WebhookInterface[]
     */
    public function getItems();

    /**
     * Set event_name list.
     *
     * @param WebhookInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
