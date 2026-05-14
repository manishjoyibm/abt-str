<?php

namespace Abbott\ProgressiveDiscount\Api;

use Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface;
use Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ManageDiscountCodesRepositoryInterface
{

    /**
     * Save ManageDiscountCodes
     *
     * @param ManageDiscountCodesInterface $manageDiscountCodes
     * @return ManageDiscountCodesInterface
     * @throws LocalizedException
     */
    public function save(
        ManageDiscountCodesInterface $manageDiscountCodes
    );

    /**
     * Retrieve ManageDiscountCodes
     *
     * @param string $managediscountcodesId
     * @return ManageDiscountCodesInterface
     * @throws LocalizedException
     */
    public function get($rowId);

    /**
     * Retrieve ManageDiscountCodes matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ManageDiscountCodesSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ManageDiscountCodes
     *
     * @param ManageDiscountCodesInterface $manageDiscountCodes
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        ManageDiscountCodesInterface $manageDiscountCodes
    );

    /**
     * Delete ManageDiscountCodes by ID
     *
     * @param string $managediscountcodesId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($rowId);
}
