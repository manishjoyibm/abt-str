<?php

namespace Abbott\MetabolicOrdering\Plugin\Order\Create;
use Abbott\MetabolicOrdering\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Items
{
    const MESSAGE = 'Product requires pre-approval to purchase';
     /**
      * @var helper
      */
    protected $helper;

    protected $timezoneInterface;

     /**
      * Constructor
      *
      * @param Data $helper
      */
    public function __construct(
        TimezoneInterface $timezoneInterface,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->timezoneInterface = $timezoneInterface;
    }
    
     public function afterGetItems(\Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid $subject, $items)
    {
        if($this->helper->getModuleEnable()){  
            $items = $subject->getParentBlock()->getItems();
              foreach ($items as $item) {
                $data['customer_email'] = $subject->getQuote()->getCustomerEmail();
                $data['sku'] = $item->getProduct()->getSku();
                $result_data = $this->helper->ifExistingRecord($data);
                $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
                $level1 = $this->helper->getAttrOptIdByLabel();  
                $approval_attr = $this->helper->getLevelAttributeId($data['sku']);  
                if($approval_attr !== null && $approval_attr == $level1) {
                    if(empty($result_data)){
                        $item->clearMessage();
                        $item->setMessage(self::MESSAGE);
                        $item->setHasError(true);
                    } elseif($item->getQty() > $result_data['qty'] && $currentDate <= $result_data['expiry_date'] && $result_data['qty'] == 0){
                        $item->clearMessage();
                        $item->setMessage(self::MESSAGE);
                        $item->setHasError(true);
                    } elseif($item->getQty() > $result_data['qty'] && $currentDate <= $result_data['expiry_date']){
                        $item->clearMessage();
                        $item->setMessage(__('Maximum QTY allowed for this product is '.$result_data['qty']));
                        $item->setHasError(true);
                    } elseif($currentDate > $result_data['expiry_date']){
                        $item->clearMessage();
                        $item->setMessage(self::MESSAGE);
                        $item->setHasError(true);
                    }
                }
            }
        }
        return $items;
    }
}