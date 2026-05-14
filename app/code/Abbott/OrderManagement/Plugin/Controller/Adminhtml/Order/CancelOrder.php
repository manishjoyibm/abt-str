<?php

namespace Abbott\OrderManagement\Plugin\Controller\Adminhtml\Order;

use Magento\Store\Model\ScopeInterface;

class CancelOrder
{
  public $request;
    public $helper;
    /**
   * Cancel constructor.
   *
   * @param RequestInterface $request
   * @param Data $helper
   */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Abbott\OrderManagement\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
    }

    public function afterExecute(\Magento\Sales\Controller\Adminhtml\Order\Cancel $subject, $result)
    {
        if ($this->helper->getMailEnabled()) {
            $orderId = $this->request->getParam("order_id");
            $this->helper->sendCancelNotification($orderId);
        }
        return $result;
    }
}
