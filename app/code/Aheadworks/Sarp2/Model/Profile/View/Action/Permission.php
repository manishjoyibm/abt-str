<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\View\Action;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp2\Model\Config;

/**
 * Class Permission
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Action
 */
class Permission
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ProfileManagementInterface $profileManagement
     * @param ProfileRepositoryInterface $profileRepository
     * @param Config $config
     */
    public function __construct(
        ProfileManagementInterface $profileManagement,
        ProfileRepositoryInterface $profileRepository,
        Config $config
    ) {
        $this->profileManagement = $profileManagement;
        $this->profileRepository = $profileRepository;
        $this->config = $config;
    }

    /**
     * Check if cancel action available
     *
     * @param int $profileId
     * @return bool
     * @throws LocalizedException
     */
    public function isCancelActionAvailable($profileId)
    {
        $allowedStatuses = $this->profileManagement->getAllowedStatuses($profileId);
        return in_array(StatusSource::CANCELLED, $allowedStatuses);
    }

    /**
     * Check if edit action available
     *
     * @param int $profileId
     * @return bool
     * @throws LocalizedException
     */
    public function isEditActionAvailable($profileId)
    {
        return $this->isCancelActionAvailable($profileId);
    }

    /**
     * Check if edit plan action is available
     *
     * @param int $profileId
     * @return bool
     * @throws LocalizedException
     */
    public function isEditPlanActionAvailable($profileId)
    {
        return $this->isCancelActionAvailable($profileId) && $this->config->canSwitchToAnotherPlan();
    }

    /**
     * Check if edit next payment date action is available
     *
     * @param int $profileId
     * @return bool
     * @throws LocalizedException
     */
    public function isEditNextPaymentDateActionAvailable($profileId)
    {
        return $this->isCancelActionAvailable($profileId)
            && $this->config->canEditNextPaymentDate()
            && $this->isEditNextPaymentDateAllowedForMembership($profileId);
    }

    /**
     * Check if edit address action is available
     *
     * @param int $profileId
     * @return bool
     * @throws LocalizedException
     */
    public function isEditAddressActionAvailable($profileId)
    {
        $profile = $this->profileRepository->get($profileId);
        return $this->isEditActionAvailable($profile->getProfileId())
            && !$profile->getIsVirtual()
            && $this->config->canChangeSubscriptionAddress();
    }

    /**
     * Check if edit next payment date is allowed for membership
     *
     * @param int $profileId
     * @return bool
     * @throws LocalizedException
     */
    private function isEditNextPaymentDateAllowedForMembership($profileId)
    {
        $profile = $this->profileRepository->get($profileId);
        $isMembershipEnabled = $profile->getProfileDefinition()->getIsMembershipModelEnabled();

        return $isMembershipEnabled ? $this->config->canEditNextPaymentDateForMembership() : true;
    }
}
