<?php

namespace Abbott\Impersonation\Controller\Adminhtml\Login;

class Login extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Url
     */
    protected $url = null;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Abbott\Impersonation\Model\Impersonation
     */
    protected $impersonationModel;
    /**
     * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    protected $resultRedirect;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession  = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager  = null;

    /**
     * Login constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Abbott\Impersonation\Model\Impersonation $impersonationModel
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Url $url
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Abbott\Impersonation\Model\Impersonation $impersonationModel,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirect,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Url $url
    ) {
        parent::__construct($context);
        $this->impersonationModel = $impersonationModel;
        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
        $this->resultRedirect = $resultRedirect;
        $this->customerFactory = $customerFactory;
        $this->url = $url;
    }

    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $customerId = (int) $request->getParam('id');
        $customerStoreId = $request->getParam('store');

        $impersonation = $this->impersonationModel->setCustomerId($customerId);

        $customer = $impersonation->getCustomer();

        if (!$customer->getId()) {
            return $this->resultRedirect->setPath('customer/index/index');
        }

        $user = $this->authSession->getUser();
        $impersonation->generate($user->getId());

        if (!$customerStoreId) {
            $customerStoreId = $this->getCustomerStoreId($customer);
        }

        if ($customerStoreId) {
            $store = $this->storeManager->getStore($customerStoreId);
        } else {
            $store = $this->storeManager->getDefaultStoreView();
        }
        $redirectUrl = $this->url->setScope($store)
            ->getUrl('impersonation/login/index', ['secret' => $impersonation->getSecret(), '_nosid' => true]);

        $this->getResponse()->setRedirect($redirectUrl);
    }

    /**
     * @param $customer
     * @return string
     */
    public function getCustomerStoreId(\Magento\Customer\Model\Customer $customer)
    {
        return $customer->getData('store_id');
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Abbott_Impersonation::login_button');
    }
}
