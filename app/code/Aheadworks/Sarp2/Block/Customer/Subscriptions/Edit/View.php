<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Block\Customer\Subscription;
use Aheadworks\Sarp2\Model\Plan\TitleResolver;
use Aheadworks\Sarp2\Model\Profile\Address\Renderer as AddressRenderer;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Directory\Model\CurrencyFactory;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Framework\View\Element\Template;

/**
 * Class View
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit
 */
class View extends Subscription
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProfileManagementInterface $profileManagement
     * @param StatusSource $statusSource
     * @param ProductRepositoryInterface $productRepository
     * @param ProductUrl $productUrl
     * @param CurrencyFactory $currencyFactory
     * @param ActionPermission $actionPermission
     * @param AddressRenderer $addressRenderer
     * @param PlanRepositoryInterface $planRepository
     * @param TitleResolver $titleResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProfileManagementInterface $profileManagement,
        StatusSource $statusSource,
        ProductRepositoryInterface $productRepository,
        ProductUrl $productUrl,
        CurrencyFactory $currencyFactory,
        ActionPermission $actionPermission,
        AddressRenderer $addressRenderer,
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
        $this->registry = $registry;
        $this->addressRenderer = $addressRenderer;
    }

    /**
     * Retrieve profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->registry->registry('profile');
    }

    /**
     * Get subscription plan edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getSubscriptionPlanEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/plan',
            ['profile_id' => $profileId]
        );
    }

    /**
     * Get subscription plan edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getNextPaymentDateEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/nextPaymentDate',
            ['profile_id' => $profileId]
        );
    }

    /**
     * Get shipping address edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getShippingAddressEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/address',
            ['profile_id' => $profileId]
        );
    }

    /**
     * Get payment details edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getPaymentDetailsEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/payment',
            ['profile_id' => $profileId]
        );
    }

    /**
     * Retrieve string with formatted address
     *
     * @param ProfileAddressInterface $address
     * @return null|string
     */
    public function getFormattedAddress($address)
    {
        return $this->addressRenderer->render($address);
    }

    /**
     * Retrieve payment details html
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getPaymentDetailsHtml($profile)
    {
        $paymentDetailsHtml = '';
        /** @var Template $paymentDetailsTemplate */
        $paymentDetailsTemplate = $this->getChildBlock(
            'aw_sarp2.customer.subscriptions.edit.view.payment.details'
        );
        if ($paymentDetailsTemplate && $paymentDetailsTemplate instanceof Template) {
            $paymentDetailsTemplate->assign('profile', $profile);
            $paymentDetailsHtml = $paymentDetailsTemplate->toHtml();
        }
        return $paymentDetailsHtml;
    }
}
