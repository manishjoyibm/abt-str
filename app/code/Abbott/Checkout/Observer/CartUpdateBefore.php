<?php

namespace Abbott\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use \Psr\Log\LoggerInterface;

class CartUpdateBefore implements ObserverInterface
{
    protected $helperData;

    public function __construct(
        \Abbott\Checkout\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

     /**
      * Check move quote item to wishlist request
      *
      * @param   Observer $observer
      * @return  $this
      */
    public function execute(Observer $observer)
    {
        if ($this->helperData->isEnabledQuantityValidation()) {
            $cart = $observer->getEvent()->getCart();
            $data = $observer->getEvent()->getInfo()->toArray();
            foreach ($data as $itemId => $itemInfo) {
                if ($itemInfo['qty'] > 0) {
                    $item = $cart->getQuote()->getItemById($itemId);
                    $productId = $item->getProductId();
                    $qty = $itemInfo['qty'];
                    if ($this->helperData->getSubscriptionOption($item)) {
                        $isValidQty = $this->helperData->validateProductQuantity($productId, $qty);
                        if (!empty($isValidQty)) {
                            throw new \Magento\Framework\Exception\LocalizedException(__($isValidQty));
                        }
                    }
                }
            }
        }
        return $this;
    }
}
