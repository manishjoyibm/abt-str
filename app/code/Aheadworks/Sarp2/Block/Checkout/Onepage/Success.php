<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Checkout\Onepage;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;

/**
 * Class Success
 *
 * @method bool getCanViewProfiles()
 *
 * @package Aheadworks\Sarp2\Block\Checkout\Onepage
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
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
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $orderConfig
     * @param HttpContext $httpContext
     * @param ProfileRepositoryInterface $profileRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        ProfileRepositoryInterface $profileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->profileRepository = $profileRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareBlockData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getIncrementId()) {
            parent::prepareBlockData();
        }
        $this->addData(['can_view_profiles' => false]);
    }

    /**
     * Get profiles
     *
     * @return ProfileInterface[]
     */
    public function getProfiles()
    {
        $profiles = [];
        $profileIds = $this->_checkoutSession->getLastProfileIds();
        if ($profileIds) {
            $this->_checkoutSession->setLastProfileIds(null);
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(ProfileInterface::PROFILE_ID, $profileIds, 'in')
                ->create();
            $profiles = $this->profileRepository->getList($searchCriteria)
                ->getItems();
        }
        return $profiles;
    }

    /**
     * Get view profile url
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getViewProfileUrl($profile)
    {
        return $this->getUrl(
            'aw_sarp2/profile/view',
            ['profile_id' => $profile->getProfileId()]
        );
    }
}
