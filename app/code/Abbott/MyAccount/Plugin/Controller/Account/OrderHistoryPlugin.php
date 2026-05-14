<?php

namespace Abbott\MyAccount\Plugin\Controller\Account;

use Abbott\MyAccount\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Sales\Controller\Order\History;
use Magento\Sales\Model\OrderFactory;

class OrderHistoryPlugin
{
    public $customerSession;
    public $accountHelper;
    public $helper;
    public $response;
    protected $orderFactory;

    /**
     * Construct function
     *
     * @param Session $customerSession
     * @param Data $accountHelper
     * @param \Abbott\CustomerTransistion\Helper\Data $helper
     * @param Http $response
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Session $customerSession,
        Data $accountHelper,
        \Abbott\CustomerTransistion\Helper\Data $helper,
        Http $response,
        OrderFactory $orderFactory
    ) {
            $this->customerSession = $customerSession;
            $this->accountHelper = $accountHelper;
            $this->helper = $helper;
            $this->response = $response;
            $this->orderFactory = $orderFactory;
    }

    /**
     * BeforeExecute function
     *
     * @param History $subject
     * @return void
     */
    public function beforeExecute(History $subject)
    {
        if ($this->customerSession->isLoggedIn() &&
            $this->accountHelper->getRedirectConfig('is_no_orders_redirect_enabled')) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $orders = $this->orderFactory->create()->getCollection()
                ->addFieldToFilter(
                    'customer_id',
                    $customerId
                );
            if ($orders->getSize() == 0) {
                $this->response->setRedirect(
                    $this->helper->getFailureUrl() . $this->accountHelper->getRedirectConfig('aem_no_order_page')
                );
            }
        }
    }
}
