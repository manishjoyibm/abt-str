<?php
namespace Abbott\CartRuleMessage\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Abbott\CartRuleMessage\Helper\Data as dataHelper;

class Coupons implements ArgumentInterface
{
     /**
      * @var DataHelper
      */
    protected $dataHelper;
    public function __construct(
        DataHelper $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get admin configuration value
     *
     * @return mixed
     */
    public function getAdminEnable()
    {
        return $this->dataHelper->getAdminEnable();
    }

    /**
     * Get checkout Message
     *
     * @return int
     */
    public function getCheckoutMessage($couponCode)
    {
        return $this->dataHelper->getCheckoutMessage($couponCode);
    }
}
