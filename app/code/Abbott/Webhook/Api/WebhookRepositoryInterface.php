<?php


namespace Abbott\Webhook\Api;

use Abbott\Webhook\Api\Data\WebhookInterface;
use Abbott\Webhook\Api\Data\WebhookSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WebhookRepositoryInterface
{

    /**
     * Save webhook
     *
     * @param WebhookInterface $webhook
     * @return WebhookInterface
     * @throws LocalizedException
     */
    public function save(
        WebhookInterface $webhook
    );

    /**
     * Retrieve webhook
     *
     * @param string $webhookId
     * @return WebhookInterface
     * @throws LocalizedException
     */
    public function get($webhookId);

    /**
     * Retrieve webhook matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return WebhookSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete webhook
     *
     * @param WebhookInterface $webhook
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        WebhookInterface $webhook
    );

    /**
     * Delete webhook by ID
     *
     * @param string $webhookId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($webhookId);
}
