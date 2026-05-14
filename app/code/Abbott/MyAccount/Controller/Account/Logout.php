<?php

namespace Abbott\MyAccount\Controller\Account;

use Abbott\CustomerTransistion\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sign out a customer.
 */
class Logout extends AbstractAccount implements HttpGetActionInterface, HttpPostActionInterface
{
    public $tokenModelCollectionFactory;
    public $accountHelper;
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     *
     * @var Customer
     */
    protected $customer;

    /**
     *
     * @var Data
     */
    protected $helper;

    /**
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     *
     * @var StoreManagerInterface
     */
    protected $storemanager;

    /**
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * Construct function
     *
     * @param Context $context
     * @param Session $customerSession
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Abbott\MyAccount\Helper\Data $accountHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        TokenCollectionFactory $tokenModelCollectionFactory,
        \Abbott\MyAccount\Helper\Data $accountHelper
    ) {
        $this->session = $customerSession;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->accountHelper = $accountHelper;
        parent::__construct($context);
    }

    /**
     * Retrieve cookie manager
     *
     * @return PhpCookieManager|mixed
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(PhpCookieManager::class);
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return CookieMetadataFactory|mixed
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * RevokeCustomerAccessToken function
     *
     * @param int $customerId
     * @return bool
     */
    private function revokeCustomerAccessToken($customerId)
    {
        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFilterByCustomerId($customerId);
        if ($tokenCollection->getSize() == 0) {
            return false;
        }
        try {
            foreach ($tokenCollection as $token) {
                $token->delete();
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Customer logout action
     *
     * @return Redirect
     * @throws InputException
     * @throws FailureToSendException
     */
    public function execute()
    {
        $lastCustomerId = $this->session->getId();
        $this->session->logout()->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastCustomerId($lastCustomerId);
        if ($lastCustomerId) {
            $this->revokeCustomerAccessToken($lastCustomerId);
        }
        $this->accountHelper->removeCookie('abt_usr');
        $this->accountHelper->removeCookie('abt_sesCartKey');
        $this->accountHelper->removeCookie('abt_cartKey');
        $this->accountHelper->removeCookie('abt_asm');
        $this->accountHelper->removeCookie('abt_te');
        $this->accountHelper->removeCookie('abt_sgp');
        $this->accountHelper->removeCookie('abt_psrid');
        $this->accountHelper->removeCookie('form_key');
        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('redirect')) {
            $resultRedirect->setPath('*/*/login');
        } else {
            $resultRedirect->setPath('*/*/logoutSuccess');
        }
        return $resultRedirect;
    }
}
