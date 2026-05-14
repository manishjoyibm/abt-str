<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Model\Profile\Address\Resolver\FullName as FullNameResolver;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Model\CountryFactory;
use Magento\Payment\Api\PaymentMethodListInterface;

/**
 * Class Addresses
 * @package Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info
 */
class Addresses extends Template
{
    /**
     * @var FullNameResolver
     */
    private $fullNameResolver;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::subscription/info/addresses.phtml';

    /**
     * @param Context $context
     * @param FullNameResolver $fullNameResolver
     * @param CountryFactory $countryFactory
     * @param PaymentMethodListInterface $paymentMethodList
     * @param array $data
     */
    public function __construct(
        Context $context,
        FullNameResolver $fullNameResolver,
        CountryFactory $countryFactory,
        PaymentMethodListInterface $paymentMethodList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->fullNameResolver = $fullNameResolver;
        $this->countryFactory = $countryFactory;
        $this->paymentMethodList = $paymentMethodList;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * todo: M2SARP-382
     * Get full name
     *
     * @param ProfileAddressInterface $address
     * @return string
     */
    public function getFullName($address)
    {
        return $this->fullNameResolver->getFullName($address);
    }

    /**
     * Get country name
     *
     * @param string $countryId
     * @return string
     */
    public function getCountryName($countryId)
    {
        $country = $this->countryFactory->create()->loadByCode($countryId);
        return $country->getName();
    }

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getPaymentMethodTitle()
    {
        $profile = $this->getProfile();
        $methods = $this->paymentMethodList->getList($profile->getStoreId());
        foreach ($methods as $method) {
            if ($method->getCode() == $profile->getPaymentMethod()) {
                return $method->getTitle();
            }
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }
}
