<?php

namespace Abbott\WorkdayFeed\Api;

use Abbott\WorkdayFeed\Api\Data\InboundFeedInterface;
use Abbott\WorkdayFeed\Api\Data\InboundFeedSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * InboundFeed CRUD interface.
 * @api
 */
interface InboundFeedRepositoryInterface
{

    /**
     * Save Inbound Feed
     *
     * @param InboundFeedInterface  $feed
     * @return InboundFeedInterface
     * @throws LocalizedException
     */
    public function save(InboundFeedInterface $feed): InboundFeedInterface;

    /**
     * Retrieve Inbound Feed
     *
     * @param int $feedId
     * @return InboundFeedInterface
     * @throws LocalizedException
     */
    public function getById(int $feedId): InboundFeedInterface;

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
     * @param InboundFeedInterface $feedId
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(InboundFeedInterface $feedId): bool;

    /**
     * Delete Inbound Feed by ID.
     *
     * @param int $feedId
     * bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $feedId);
}
