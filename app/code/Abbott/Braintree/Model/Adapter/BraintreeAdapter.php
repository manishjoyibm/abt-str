<?php


namespace Abbott\Braintree\Model\Adapter;


use Braintree\PaymentMethodNonce;

class BraintreeAdapter extends \PayPal\Braintree\Model\Adapter\BraintreeAdapter
{
    /**
     * @param string $token
     * @return PaymentMethodNonce
     */
    public function findNonce($token)
    {
        return PaymentMethodNonce::find($token);
    }
}
