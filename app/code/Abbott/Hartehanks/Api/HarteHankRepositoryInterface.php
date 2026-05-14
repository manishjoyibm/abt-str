<?php

namespace Abbott\Hartehanks\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface HarteHankRepositoryInterface
{

    /**
     * Save HarteHank
     * @param \Abbott\Hartehanks\Api\Data\HarteHankInterface $harteHank
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Abbott\Hartehanks\Api\Data\HarteHankInterface $harteHank
    );

    /**
     * Retrieve HarteHank
     * @param string $hartehankId
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($hartehankId);

    /**
     * Retrieve HarteHank matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\Hartehanks\Api\Data\HarteHankSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete HarteHank
     * @param \Abbott\Hartehanks\Api\Data\HarteHankInterface $harteHank
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Abbott\Hartehanks\Api\Data\HarteHankInterface $harteHank
    );

    /**
     * Delete HarteHank by ID
     * @param string $hartehankId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($hartehankId);
}
