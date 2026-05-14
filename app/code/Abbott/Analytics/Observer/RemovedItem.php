<?php

namespace Abbott\Analytics\Observer;

use Magento\Framework\Event\Observer;

class RemovedItem implements \Magento\Framework\Event\ObserverInterface
{
    protected $checkoutSession;

    /**
     * RemovedItem constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSesssion
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSesssion
    ) {
        $this->checkoutSession = $checkoutSesssion;
    }

    public function execute(Observer $observer)
    {
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();
        $items = $this->checkoutSession->getRemovedItems();
        $itemArr = [
            "id"    => $product->getId(),
            "price" => $item->getPrice(),
            "qty"   => $item->getQty()
        ];

        if ($items) {
            array_push($items, $itemArr);
            $this->checkoutSession->setRemovedItems($items);
        } else {
            $this->checkoutSession->setRemovedItems([$itemArr]);
        }
    }
}
