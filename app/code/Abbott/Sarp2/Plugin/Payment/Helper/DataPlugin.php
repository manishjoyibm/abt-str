<?php
namespace Abbott\Sarp2\Plugin\Payment\Helper;

use Magento\Payment\Helper\Data;

class DataPlugin
{
    /**
     * Modify results of getPaymentMethodList() call to add additional method in grid
     *
     * @param Data $subject
     * @param $result
     * @return array
     */
    public function afterGetPaymentMethodList(Data $subject, $result,$sorted = true, $asLabelValue = false, $withGroups = false, $store = null){
        $result['aw_sarp_braintree_recurring'] = ['value' => 'aw_sarp_braintree_recurring', 'label' => 'Credit Card'];
        $result['aw_sarp_braintree_paypal_recurring'] = ['value' => 'aw_sarp_braintree_paypal_recurring', 'label' => 'PayPal'];
        $result['aw_sarp_braintree_googlepay_recurring'] = ['value' => 'aw_sarp_braintree_googlepay_recurring', 'label' => 'Google Pay'];
        $result['aw_sarp_braintree_applepay_recurring'] = ['value' => 'aw_sarp_braintree_applepay_recurring', 'label' => 'Apple Pay'];
        return $result;
    }
}
