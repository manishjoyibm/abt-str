<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Request
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $profile = $paymentDO->getProfile();
        $billingAddress = $profile->getBillingAddress();

        $result = [];
        if ($billingAddress) {
            $street = $billingAddress->getStreet();
            $streetAddress = is_array($street)
                ? isset($street[0]) ? $street[0] : ''
                : $street;
            $extendedAddress = is_array($street) && isset($street[1]) ? $street[1] : '';
            $result['billing'] = [
                'firstName' => $billingAddress->getFirstname(),
                'lastName' => $billingAddress->getLastname(),
                'company' => $billingAddress->getCompany(),
                'streetAddress' => $streetAddress,
                'extendedAddress' => $extendedAddress,
                'locality' => $billingAddress->getCity(),
                'region' => $billingAddress->getRegionCode(),
                'postalCode' => $billingAddress->getPostcode(),
                'countryCodeAlpha2' => $billingAddress->getCountryId()
            ];
        }

        $shippingAddress = $profile->getShippingAddress();
        if ($shippingAddress) {
            $street = $shippingAddress->getStreet();
            $streetAddress = is_array($street)
                ? isset($street[0]) ? $street[0] : ''
                : $street;
            $extendedAddress = is_array($street) && isset($street[1]) ? $street[1] : '';
            $result['shipping'] = [
                'firstName' => $shippingAddress->getFirstname(),
                'lastName' => $shippingAddress->getLastname(),
                'company' => $shippingAddress->getCompany(),
                'streetAddress' => $streetAddress,
                'extendedAddress' => $extendedAddress,
                'locality' => $shippingAddress->getCity(),
                'region' => $shippingAddress->getRegionCode(),
                'postalCode' => $shippingAddress->getPostcode(),
                'countryCodeAlpha2' => $shippingAddress->getCountryId()
            ];
        }

        return $result;
    }
}
