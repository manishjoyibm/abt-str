<?php

namespace Abbott\GlucernaOrders\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ManagesubscriptionRepositoryInterface
{
    /**
     * Save managesubscription
     * @param \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface $managesubscription
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface $managesubscription
    );

    /**
     * Retrieve managesubscription
     * @param string $managesubscriptionId
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($managesubscriptionId);

    /**
     * Retrieve managesubscription matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete managesubscription
     * @param \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface $managesubscription
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface $managesubscription
    );

    /**
     * Delete managesubscription by ID
     * @param string $managesubscriptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($managesubscriptionId);
}
