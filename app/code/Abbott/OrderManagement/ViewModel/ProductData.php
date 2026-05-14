<?php
namespace Abbott\OrderManagement\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductData implements ArgumentInterface
{
    protected $helperData;
    
    public function __construct(
        \Abbott\OrderManagement\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

     /**
      * get Call To Order status
      *
      * @param order
      * @return flag
      */
    public function getOrderOnCall($order)
    {
        return $this->helperData->getOrderOnCall($order);
    }
}
