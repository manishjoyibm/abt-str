<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Abbott\PowerbiExport\Api;

use Abbott\PowerbiExport\Api\Data\PowerbiInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface PowerbiExportRepositoryInterface
{

    /**
     * Save PowerbiExport
     *
     * @param PowerbiInterface $powerbiExport
     * @return PowerbiInterface
     * @throws LocalizedException
     */
    public function save(
        PowerbiInterface $powerbiExport
    );

    /**
     * Retrieve PowerbiExport
     * @param int $powerbiExportId
     * @return PowerbiInterface
     * @throws LocalizedException
     */
    public function get(int $powerbiExportId);

    /**
     * Retrieve PowerbiExport matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\PowerbiExport\Api\Data\PowerbiExportSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PowerbiExport
     * @param PowerbiInterface $PowerbiExport
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        PowerbiInterface $powerbiExport
    );

    /**
     * Delete PowerBI Export by ID
     * @param int $powerbiExportId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $powerbiExportId);
}
