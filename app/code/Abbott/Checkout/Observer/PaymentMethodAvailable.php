<?php
namespace Abbott\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $googlepayCode = "aw_sarp_braintree_googlepay_recurring";
        $applepayCode = "aw_sarp_braintree_applepay_recurring";
        if ($observer->getEvent()->getMethodInstance()->getCode()== $googlepayCode ||
            $observer->getEvent()->getMethodInstance()->getCode()== $applepayCode
        ) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }
}
