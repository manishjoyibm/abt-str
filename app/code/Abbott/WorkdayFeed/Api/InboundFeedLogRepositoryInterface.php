<?php

namespace Abbott\WorkdayFeed\Api;

use Abbott\WorkdayFeed\Api\Data\InboundFeedLogInterface;
use Abbott\WorkdayFeed\Api\Data\InboundFeedSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * InboundFeed Log CRUD interface.
 * @api
 */
interface InboundFeedLogRepositoryInterface
{

    /**
     * Save Inbound Feed
     *
     * @param InboundFeedLogInterface  $feedLog
     * @return InboundFeedLogInterface
     * @throws LocalizedException
     */
    public function save(InboundFeedLogInterface $feedLog): InboundFeedLogInterface;

    /**
     * Retrieve Inbound Feed
     *
     * @param int $feedLogId
     * @return InboundFeedLogInterface
     * @throws LocalizedException
     */
    public function getById(int $feedLogId): InboundFeedLogInterface;

    /**
     * Retrieve Inbound feed matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return InboundFeedSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): InboundFeedSearchResultsInterface;

    /**
     * Delete Inbound Feed
     *
     * @param InboundFeedLogInterface $feedId
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(InboundFeedLogInterface $feedId): bool;

    /**
     * Delete Inbound Feed by ID.
     *
     * @param int $feedLogId
     * bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $feedLogId);
}
