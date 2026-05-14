<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Aheadworks\Sarp2\Model\Profile\Address\Renderer as AddressRenderer;
use Magento\Customer\Model\Address\Config;

/**
 * Class Address
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit
 */
class Address extends Template
{
    /**
     * Url param for redirect to sarp2 profile
     */
    const REDIRECT_TO_SARP2_PROFILE = 'redirectToSarp2ProfileId';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRenderer $addressRenderer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        AddressRenderer $addressRenderer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
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
     * Retrieve customer address
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerAddress()
    {
        $customer = $this->customerRepository->getById($this->getProfile()->getCustomerId());
        $addresses = [0 => __('Please Select New Address')];
        foreach ($customer->getAddresses() as $address) {
            $addresses[$address->getId()] = $this->addressRenderer->render($address, Config::DEFAULT_ADDRESS_FORMAT);
        }

        return $addresses;
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
     * Retrieve save url
     *
     * @param int $profileId
     * @return string
     */
    public function getSaveUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/saveAddress',
            ['profile_id' => $profileId]
        );
    }

    /**
     * Retrieve add new address url
     *
     * @param int $profileId
     * @return string
     */
    public function getAddAddressUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'customer/address/new/',
            [self::REDIRECT_TO_SARP2_PROFILE => $profileId]
        );
    }
}
