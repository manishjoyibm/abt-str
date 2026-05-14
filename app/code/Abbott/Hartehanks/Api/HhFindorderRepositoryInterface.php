<?php

namespace Abbott\Hartehanks\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface HhFindorderRepositoryInterface
{
    /**
     * Save HhFindorder
     * @param \Abbott\Hartehanks\Api\Data\HhFindorderInterface $hhFindorder
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Abbott\Hartehanks\Api\Data\HhFindorderInterface $hhFindorder
    );

    /**
     * Retrieve HhFindorder
     * @param string $hhfindorderId
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($hhfindorderId);

    /**
     * Retrieve HhFindorder matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete HhFindorder
     * @param \Abbott\Hartehanks\Api\Data\HhFindorderInterface $hhFindorder
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Abbott\Hartehanks\Api\Data\HhFindorderInterface $hhFindorder
    );

    /**
     * Delete HhFindorder by ID
     * @param string $hhfindorderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($hhfindorderId);
}
