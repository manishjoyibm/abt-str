<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Finder
 * @package Aheadworks\Sarp2\Model\Customer
 */
class Finder
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->profileRepository = $profileRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get active profiles
     *
     * @param int $customerId
     * @return ProfileInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getActiveProfilesByCustomerId($customerId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProfileInterface::CUSTOMER_ID, $customerId, 'eq')
            ->addFilter(ProfileInterface::STATUS, Status::ACTIVE, 'eq');

        /** @var ProfileSearchResultsInterface $searchResult */
        $searchResults = $this->profileRepository->getList($this->searchCriteriaBuilder->create());

        return $searchResults->getItems();
    }
}
