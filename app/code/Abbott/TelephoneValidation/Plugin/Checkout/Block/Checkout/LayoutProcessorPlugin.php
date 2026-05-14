<?php

namespace Abbott\TelephoneValidation\Plugin\Checkout\Block\Checkout;

use Abbott\TelephoneValidation\Helper\Data as dataHelper;

class LayoutProcessorPlugin
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;

    public function __construct(
        DataHelper $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, array $jsLayout)
    {
        if ($this->dataHelper->getModuleEnable()) {
            /*validation on shipping form*/
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = [
                    'required-entry' => true,
                    'min_text_length' => 1,
                    'max_text_length' => 12,
                    'validate-abt-numeric-with-hyphen-spaces' => 1
                ];
            }
            /* Validation on billing form for all payment methods enabled */
            $paymentMethodRenders = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children'];
            if (is_array($paymentMethodRenders)) {
                foreach ($paymentMethodRenders as $name => $renderer) {
                    if (isset($renderer['children']) && array_key_exists('form-fields', $renderer['children'])) {
                        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                        ['children']['payment']['children']['payments-list']['children'][$name]['children']
                        ['form-fields']['children']['telephone']['validation'] = [
                            'required-entry' => true,
                            'min_text_length' => 1,
                            'max_text_length' => 12,
                            'validate-abt-numeric-with-hyphen-spaces' => 1
                        ];
                    }
                }
            }
        }
        return $jsLayout;
    }
}
