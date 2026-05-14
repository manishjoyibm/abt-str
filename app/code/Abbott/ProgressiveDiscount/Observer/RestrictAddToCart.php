<?php

namespace Abbott\ProgressiveDiscount\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Abbott\ProgressiveDiscount\Helper\Data as HelperData;

class RestrictAddToCart implements ObserverInterface
{
    public $helper;
    /**
     *
     * @var Session
     */
    protected $customerSession;

    /**
     *
     * @var HelperData
     */
    protected $data;
    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     *
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     *
     * @param Session $customerSession
     * @param HelperData $data
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        Session $customerSession,
        HelperData $data,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->helper = $data;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->response = $response;
        $this->urlInterface = $urlInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if ($this->customerSession->isLoggedIn() && !empty($this->helper->getIsProgressiveCheckoutRestricted())) {
            // check for any progressive discount ongoing for customer
            $customerId = $this->customerSession->getCustomer()->getId();
            $quoteItems = $observer->getEvent()->getSource()->getQuoteItem();
            if ($this->helper->isSubscriptionActive($customerId)) {
                $flag = $this->helper->checkCartItems($quoteItems, 'active');
                if ($flag) {
                    $message = (!empty($this->helper->getActiveSubscriptionCheckoutMessage()) ?
                        $this->helper->getActiveSubscriptionCheckoutMessage() :
                        __('You already have an item in your cart that uses this special subscrfiption offer')
                    );
                    $this->messageManager->addError($message);
                    return $this->response->setRedirect($this->urlInterface->getUrl('checkout/cart'));
                }
            } else {
                $flag = $this->helper->checkCartItems($quoteItems);
                if ($flag) {
                    $message = (!empty($this->helper->getProductSubscriptionCheckoutMessage()) ?
                        $this->helper->getProductSubscriptionCheckoutMessage() :
                        __('You already have an active subscription that uses this special offer for an average savings of 20%')
                    );
                    $this->messageManager->addError($message);
                    return $this->response->setRedirect($this->urlInterface->getUrl('checkout/cart'));
                }
            }
        }
    }
}
