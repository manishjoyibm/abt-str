<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Plan\TitleResolver;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class Subscriptions
 * @package Aheadworks\Sarp2\Block\Customer
 */
class Subscriptions extends Subscription
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param ProfileManagementInterface $profileManagement
     * @param StatusSource $statusSource
     * @param ProductRepositoryInterface $productRepository
     * @param ProductUrl $productUrl
     * @param Session $customerSession
     * @param CurrencyFactory $currencyFactory
     * @param ActionPermission $actionPermission
     * @param PlanRepositoryInterface $planRepository
     * @param TitleResolver $titleResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        ProfileManagementInterface $profileManagement,
        StatusSource $statusSource,
        ProductRepositoryInterface $productRepository,
        ProductUrl $productUrl,
        Session $customerSession,
        CurrencyFactory $currencyFactory,
        ActionPermission $actionPermission,
        PlanRepositoryInterface $planRepository,
        TitleResolver $titleResolver,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $profileManagement,
            $statusSource,
            $productRepository,
            $productUrl,
            $currencyFactory,
            $actionPermission,
            $planRepository,
            $titleResolver,
            $data
        );
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Get profiles
     *
     * @return Collection|null
     */
    public function getProfiles()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();
            $this->collection
                ->addFieldToFilter(
                    ProfileInterface::CUSTOMER_ID,
                    ['eq' => $this->customerSession->getCustomerId()]
                )
                ->addOrder(ProfileInterface::CREATED_AT, Collection::SORT_ORDER_DESC);
        }
        return $this->collection;
    }

    /**
     * Get edit profile url
     *
     * @param int $profileId
     * @return string
     */
    public function getEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/index',
            ['profile_id' => $profileId]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getProfiles()) {
            /** @var Pager $pager */
            $pager = $this->getLayout()
                ->createBlock(
                    Pager::class,
                    'aw_sarp2.customer.subscriptions.pager'
                );
                $pager->setCollection($this->getProfiles());
            $this->setChild('pager', $pager);
            $this->getProfiles()->load();
        }
        return $this;
    }
}
