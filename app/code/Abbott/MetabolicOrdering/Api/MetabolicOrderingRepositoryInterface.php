<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface MetabolicOrderingRepositoryInterface
{

    /**
     * Save MetabolicOrdering
     * @param \Abbott\MetabolicOrdering\Api\Data\MetabolicInterface $metabolicOrdering
     * @return \Abbott\MetabolicOrdering\Api\Data\MetabolicInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Abbott\MetabolicOrdering\Api\Data\MetabolicInterface $metabolicOrdering
    );

    /**
     * Retrieve MetabolicOrdering
     * @param string $metabolicorderingId
     * @return \Abbott\MetabolicOrdering\Api\Data\MetabolicInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($metabolicorderingId);

    /**
     * Retrieve MetabolicOrdering matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\MetabolicOrdering\Api\Data\MetabolicOrderingSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete MetabolicOrdering
     * @param \Abbott\MetabolicOrdering\Api\Data\MetabolicInterface $metabolicOrdering
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Abbott\MetabolicOrdering\Api\Data\MetabolicInterface $metabolicOrdering
    );

    /**
     * Delete MetabolicOrdering by ID
     * @param string $metabolicorderingId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($metabolicorderingId);
}
