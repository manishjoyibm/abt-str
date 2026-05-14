<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Email\Sender\Enabler;

use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Email\Sender\EnablerInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class UpcomingBilling
 * @package Aheadworks\Sarp2\Model\Email\Sender\Enabler
 */
class UpcomingBilling implements EnablerInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param Config $config
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Config $config,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->config = $config;
        $this->profileRepository = $profileRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LocalizedException
     */
    public function isEnabled($notification)
    {
        $storeId = $notification->getStoreId();
        $profile = $this->profileRepository->get($notification->getProfileId());
        $profileDefinition = $profile->getProfileDefinition();

        $offset = $profileDefinition->getUpcomingBillingEmailOffset()
            ? : $this->config->getUpcomingBillingEmailOffset($storeId);

        return $offset > 0;
    }
}
