<?php

namespace Abbott\KountCustomFields\Plugin\Config;

use Kount\Kount360\Model\Config\Source\PaymentMethods;

class SourcePlugin
{
    public function afterToOptionArray(PaymentMethods $subject, $result)
    {
        $values[] = ['value' => 'aw_sarp_braintree_recurring', 'label' => 'Sarp2 Credit Card'];
        $values[] = ['value' => 'aw_sarp_braintree_paypal_recurring', 'label' => 'Sarp2 PayPal'];
        $values[] = ['value' => 'aw_sarp_braintree_googlepay_recurring', 'label' => 'Sarp2 Google Pay'];
        $values[] = ['value' => 'aw_sarp_braintree_applepay_recurring', 'label' => 'Sarp2 Apple Pay'];
        return array_merge($result, $values);
    }
}
