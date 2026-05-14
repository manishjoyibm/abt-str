<?php

namespace  Abbott\Sarp2\Plugin\Observer;
use Aheadworks\Sarp2\Observer\PaymentMethodAvailabilityObserver;
use Magento\Framework\Event\Observer as EventObserver;

class PaymentMethodAvailabilityObserverPlugin{
    /**
     * @param PaymentMethodAvailabilityObserver $subject
     * @param EventObserver $observer
     */
    public function afterExecute(PaymentMethodAvailabilityObserver $subject,$result, EventObserver $observer)
    {
        if(in_array($observer->getEvent()->getMethodInstance()->getCode(),$this->allowedPaymentMethod())){
            $observer->getEvent()->getResult()->setData('is_available', true);
        }

    }

    /**
     * @return array
     */
    public function allowedPaymentMethod(){
        return [
            \PayPal\Braintree\Model\GooglePay\Ui\ConfigProvider::METHOD_CODE,
            \PayPal\Braintree\Model\ApplePay\Ui\ConfigProvider::METHOD_CODE
        ];
    }
}
