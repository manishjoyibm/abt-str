<?php

namespace Abbott\KountCustomFields\Model\Ris\Inquiry\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\DataObject;

class NoPayment implements PaymentInterface
{
    /**
     * @param DataObject $request
     * @param OrderPaymentInterface $payment
     * @return void
     */
    public function process(DataObject $request, OrderPaymentInterface $payment)
    {
        $request->setNoPayment();
        $request->setAvst('X');
        $request->setAvsz('X');
    }
}
