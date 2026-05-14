<?php

namespace Abbott\Chargeback\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ChargebackRepositoryInterface
{
    /**
     * Save Chargeback
     * @param \Abbott\Chargeback\Api\Data\ChargebackInterface $chargeback
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Abbott\Chargeback\Api\Data\ChargebackInterface $chargeback
    );

    /**
     * Retrieve Chargeback
     * @param string $chargebackId
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($chargebackId);

    /**
     * Retrieve Chargeback matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\Chargeback\Api\Data\ChargebackSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Chargeback
     * @param \Abbott\Chargeback\Api\Data\ChargebackInterface $chargeback
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Abbott\Chargeback\Api\Data\ChargebackInterface $chargeback
    );

    /**
     * Delete Chargeback by ID
     * @param string $chargebackId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($chargebackId);
}
