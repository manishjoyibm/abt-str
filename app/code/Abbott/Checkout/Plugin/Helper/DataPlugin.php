<?php

namespace Abbott\Checkout\Plugin\Helper;

class DataPlugin
{
    const VALIDATE_FILTERS = 'validate_filters';
    const ABT_NAME = 'abt-name';
    const ABT_COMPANY = 'abt-company';
    const ABT_ZIPCODE = 'abt-zipcode';
    const MAILING_ADDRESS = 'abt-mailing-address';
    const NUMERIC_HYPHEN_SPACES = 'abt-numeric-with-hyphen-spaces';

    /**
     * Field types with allowed Name and Address validation.
     *
     * @var array
     */
    private static $allowNameValidationTypes = ['text', 'multiline'];

    /**
     * @param \Magento\CustomerCustomAttributes\Helper\Data $subject
     * @param array $result
     * @param string $inputType
     * @return array
     */
    public function afterGetAttributeInputTypes(
        \Magento\CustomerCustomAttributes\Helper\Data $subject,
        $result,
        $inputType = null
    ) {
        if (isset($result[self::VALIDATE_FILTERS]) &&
            in_array($inputType, self::$allowNameValidationTypes, true)) {
            $result[self::VALIDATE_FILTERS][] = self::ABT_NAME;
            $result[self::VALIDATE_FILTERS][] = self::ABT_COMPANY;
            $result[self::VALIDATE_FILTERS][] = self::MAILING_ADDRESS;
            $result[self::VALIDATE_FILTERS][] = self::NUMERIC_HYPHEN_SPACES;
            $result[self::VALIDATE_FILTERS][] = self::ABT_ZIPCODE;
        } else {
            foreach (self::$allowNameValidationTypes as $type) {
                if (isset($result[$type][self::VALIDATE_FILTERS])) {
                    $result[$type][self::VALIDATE_FILTERS][] = self::ABT_NAME;
                    $result[$type][self::VALIDATE_FILTERS][] = self::ABT_COMPANY;
                    $result[$type][self::VALIDATE_FILTERS][] = self::MAILING_ADDRESS;
                    $result[$type][self::VALIDATE_FILTERS][] = self::NUMERIC_HYPHEN_SPACES;
                    $result[$type][self::VALIDATE_FILTERS][] = self::ABT_ZIPCODE;
                }
            }
        }

        return $result;
    }

   /**
    * @param \Magento\CustomerCustomAttributes\Helper\Data $subject
    * @param array $result
    * @return array
    */
    public function afterGetAttributeValidateFilters(
        \Magento\CustomerCustomAttributes\Helper\Data $subject,
        $result
    ) {
        $result = array_merge($result, [self::ABT_NAME => __('ABT Name Validation')]);
        $result = array_merge($result, [self::ABT_COMPANY => __('ABT Company Validation')]);
        $result = array_merge($result, [self::MAILING_ADDRESS => __('ABT Mailing Address')]);
        $result = array_merge($result, [self::ABT_ZIPCODE => __('ABT ZipCode Validation')]);
        return array_merge($result, [self::NUMERIC_HYPHEN_SPACES => __('ABT Numeric with hyphen spaces')]);
    }
}
