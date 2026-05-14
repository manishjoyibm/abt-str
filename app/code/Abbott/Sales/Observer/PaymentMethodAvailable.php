<?php

namespace Abbott\Sales\Observer;

use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class PaymentMethodAvailable implements ObserverInterface
{
    protected $appState;
    public const CHK_PURCHASE_ORDER_CODE =
        \Magento\OfflinePayments\Model\Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE;
    public const CHECKMO = \Magento\OfflinePayments\Model\Checkmo::PAYMENT_METHOD_CHECKMO_CODE;

    /**
     * Construct function
     *
     * @param State $appState
     */
    public function __construct(
        State $appState
    ) {
        $this->appState = $appState;
    }

    /**
     * Payment_method_is_active event handler.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        //disable Purchase ORder
        if ($observer->getEvent()->getMethodInstance()->getCode() == self::CHK_PURCHASE_ORDER_CODE &&
            $this->appState->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }

        //disable Check/Money order
        if ($observer->getEvent()->getMethodInstance()->getCode() == self::CHECKMO &&
            $this->appState->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }
}
