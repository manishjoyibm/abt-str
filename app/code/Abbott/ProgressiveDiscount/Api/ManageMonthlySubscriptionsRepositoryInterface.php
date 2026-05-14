<?php

namespace Abbott\ProgressiveDiscount\Api;

use Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface;
use Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * ManageMonthlySubscriptions CRUD interface.
 * @api
 */
interface ManageMonthlySubscriptionsRepositoryInterface
{

    /**
     * Save ManageMonthlySubscriptions
     *
     * @param ManageMonthlySubscriptionsInterface  $rowId
     * @return ManageMonthlySubscriptionsInterface
     * @throws LocalizedException
     */
    public function save(ManageMonthlySubscriptionsInterface $rowId);

    /**
     * Retrieve ManageMonthlySubscriptions
     *
     * @param int $rowId
     * @return ManageMonthlySubscriptionsInterface
     * @throws LocalizedException
     */
    public function getById($rowId);

    /**
     * Retrieve ManageMonthlySubscriptions matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ManageMonthlySubscriptionsSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete ManageMonthlySubscriptions
     *
     * @param int $feedId
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ManageMonthlySubscriptionsInterface $rowId);

    /**
     * Delete ManageMonthlySubscriptions by ID.
     *
     * @param int $rowId
     * bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($rowId);
}
