<?php

namespace Abbott\Checkout\Plugin\Checkout\Block\Checkout\AttributeMerger;

use Magento\Store\Model\StoreManagerInterface;
use Abbott\MyAccount\Helper\Data as AccountHelper;

class Plugin
{
    public $storeManager;
    public $similacnewflag;
    const STREET = 'street';
    const CHILDREN = 'children';
    const PLACEHOLDER = 'placeholder';

  /**
   * @param ResourceConnection $resourceConnection
   */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
        $this->similacnewflag = false;
    }


    public function afterMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $result)
    {
        if ($this->storeManager->getStore()->getCode() == AccountHelper::NEW_SIM_STORE_CODE) {
            $this->similacnewflag = true;
        }

        if (array_key_exists(self::STREET, $result)) {
            $result[self::STREET][self::CHILDREN][0][self::PLACEHOLDER] = __('Address Line 1');
            $result[self::STREET][self::CHILDREN][1][self::PLACEHOLDER] = __('Address Line 2');
            $result[self::STREET][self::CHILDREN][2][self::PLACEHOLDER] = __('Address Line 3');
        }

        if (array_key_exists('firstname', $result)) {
            $result['firstname'][self::PLACEHOLDER] = __('First Name');
            if ($this->similacnewflag) {
                $result['firstname']['additionalClasses'] = 'fet';
                if (isset($result['firstname']['value']) && !empty($result['firstname']['value'])) {
                    $result['firstname']['additionalClasses'] = 'fet has-content';
                }
            }
        }

        if (array_key_exists('lastname', $result)) {
            $result['lastname'][self::PLACEHOLDER] = __('Last Name');
            if ($this->similacnewflag) {
                $result['lastname']['additionalClasses'] = 'fet';
                if (isset($result['lastname']['value']) && !empty($result['lastname']['value'])) {
                    $result['lastname']['additionalClasses'] = 'fet has-content';
                }
            }
        }

        if (array_key_exists('city', $result)) {
            $result['city'][self::PLACEHOLDER] = __('City');
            if ($this->similacnewflag) {
                $result['city']['additionalClasses'] = 'fet';
            }
        }

        if (array_key_exists('postcode', $result)) {
            $result['postcode'][self::PLACEHOLDER] = __('Zip Code');
            if ($this->similacnewflag) {
                $result['postcode']['additionalClasses'] = 'fet';
            }
        }

        if (array_key_exists('telephone', $result)) {
            $result['telephone'][self::PLACEHOLDER] = __('Phone');
            if ($this->similacnewflag) {
                $result['telephone']['additionalClasses'] = 'fet';
            }
        }

        if ($this->similacnewflag) {

            if (array_key_exists('street', $result)) {
                $result['street']['children'][0]['additionalClasses'] = 'fet';
            }
            if (array_key_exists('country_id', $result)) {
                $result['country_id']['additionalClasses'] = 'fet select';
            }
            if (array_key_exists('region_id', $result)) {
                $result['region_id']['additionalClasses'] = 'fet select';
            }
        }
        return $result;
    }
}
