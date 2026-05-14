<?php

namespace Abbott\Hartehanks\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface HhPlaceOrderRepositoryInterface
{
    /**
     * Save HhPlaceOrder
     * @param \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface $hhPlaceOrder
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface $hartehank
    );

    /**
     * Retrieve HhPlaceOrder
     * @param string $hartehankId
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($hartehankId);

    /**
     * Retrieve HhPlaceOrder matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete HhPlaceOrder
     * @param \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface $hartehank
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface $hartehank
    );

    /**
     * Delete HhPlaceOrder by ID
     * @param string $hartehankId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($hartehankId);
}
