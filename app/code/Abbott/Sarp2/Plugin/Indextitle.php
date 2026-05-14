<?php
namespace Abbott\Sarp2\Plugin;

use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\View\LayoutFactory;
use Abbott\MyAccount\Helper\Data as AccountHelper;

class Indextitle
{
    public $_request;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    protected $_layoutFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Customer\Model\Session $sessionSession
     * @param RequestInterface $requestInterface

     */
    public function __construct(
        \Magento\Customer\Model\Session $sessionSession,
        RequestInterface $requestInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LayoutFactory $layoutFactory
    ) {
        $this->customerSession = $sessionSession;
        $this->_request = $requestInterface;
        $this->_layoutFactory = $layoutFactory;
        $this->_storeManager = $storeManager;

    }

    public function afterExecute(
        \Aheadworks\Sarp2\Controller\Profile\Edit\Index $subject,
        $resultPage
    ) {
        if ($this->_request->getParam('profile_id') && ($this->_storeManager->getStore()->getCode() == AccountHelper::NEW_SIM_STORE_CODE)) {
            $subscriptionblockObj= $this->_layoutFactory->create()->createBlock('Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View');
            if ($subscriptionblockObj->getProfile()) {
                $retrunVal = $subscriptionblockObj->getProfile()->getIncrementId();
                $resultPage->getConfig()->getTitle()->set(__('Subscription #'.$retrunVal));
            }
        }
        return $resultPage;
    }
}
