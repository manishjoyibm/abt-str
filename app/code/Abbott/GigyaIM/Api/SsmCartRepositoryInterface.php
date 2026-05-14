<?php

namespace Abbott\GigyaIM\Api;

use Abbott\GigyaIM\Api\Data\SsmCartInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface SsmCartRepositoryInterface
{

    /**
     * Save SsmCart
     *
     * @param SsmCartInterface $ssmCart
     * @return SsmCartInterface
     * @throws LocalizedException
     */
    public function save(
        SsmCartInterface $ssmCart
    );

    /**
     * Retrieve SsmCart by id
     *
     * @param int $ssmCartId
     * @return SsmCartInterface
     * @throws LocalizedException
     */
    public function getById($ssmCartId);

    /**
     * Retrieve SsmCart
     *
     * @param string $email
     * @param int $websiteId|null
     * @return SsmCartInterface
     * @throws LocalizedException
     */
    public function getByEmail($email, $websiteId = null);

    /**
     * Retrieve ManageDiscountCodes matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ssmShoppingCart
     *
     * @param SsmCartInterface $ssmShoppingCart
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        SsmCartInterface $ssmShoppingCart
    );

    /**
     * Delete ssmShoppingCart by ID
     *
     * @param string $ssmCartId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($id);
}
