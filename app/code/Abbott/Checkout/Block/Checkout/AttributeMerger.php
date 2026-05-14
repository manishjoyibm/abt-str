<?php

namespace Abbott\Checkout\Block\Checkout;

class AttributeMerger extends \Magento\Checkout\Block\Checkout\AttributeMerger
{
    /**
     * Map input_validation and validation rule from js
     *
     * @var array
     */
    protected $inputValidationMap = [
        'alpha' => 'validate-alpha',
        'numeric' => 'validate-number',
        'alphanumeric' => 'validate-alphanum',
        'alphanum-with-spaces' => 'validate-alphanum-with-spaces',
        'url' => 'validate-url',
        'email' => 'email2',
        'length' => 'validate-length',
        'abt-name' => 'validate-abt-name',
        'abt-company' => 'validate-abt-company',
        'abt-mailing-address' => 'validate-abt-mailing-address',
        'abt-numeric-with-hyphen-spaces' => 'validate-abt-numeric-with-hyphen-spaces',
        'abt-zipcode' => 'validate-abt-zipcode'
    ];
}
