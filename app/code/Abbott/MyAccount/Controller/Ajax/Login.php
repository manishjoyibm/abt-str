<?php
namespace Abbott\MyAccount\Controller\Ajax;

use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Abbott\MyAccount\Model\MergeCart;
use Magento\Integration\Model\CustomerTokenService;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Login controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends \Magento\Customer\Controller\Ajax\Login
{
    public $mergeCartModel;
    public $storeManager;
    public $cartManagement;
    public $createEmptyCartForCustomer;
    public $checkoutSession;
    public $quoteIdMaskFactory;
    public $quoteIdToMaskedQuoteId;
    public $quoteIdMaskResourceModel;
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Data $helper
     */
    protected $helper;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    protected $customerTokenService;

    protected $accountHelper;

    const ABT_CARTKEY = 'abt_cartKey';

    private $merged = false;

    /**
     * Initialize Login controller
     *
     * @param Context $context
     * @param Session $customerSession
     * @param Data $helper
     * @param AccountManagementInterface $customerAccountManagement
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param AccountHelper $accountHelper
     * @param CustomerTokenService $customerTokenService
     * @param MergeCart $mergeCartModel
     * @param StoreManagerInterface $storeManager
     * @param CartManagementInterface $cartManagement
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     * @param CookieManagerInterface|null $cookieManager
     * @param CookieMetadataFactory|null $cookieMetadataFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Data $helper,
        AccountManagementInterface $customerAccountManagement,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        AccountHelper $accountHelper,
        CustomerTokenService $customerTokenService,
        MergeCart $mergeCartModel,
        StoreManagerInterface $storeManager,
        CartManagementInterface $cartManagement,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        CookieManagerInterface $cookieManager = null,
        CookieMetadataFactory $cookieMetadataFactory = null,
    ) {
        $this->cookieManager = $cookieManager ?:
            ObjectManager::getInstance()->get(CookieManagerInterface::class);
        $this->cookieMetadataFactory = $cookieMetadataFactory ?:
            ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        $this->accountHelper = $accountHelper;
        $this->customerTokenService = $customerTokenService;
        $this->mergeCartModel = $mergeCartModel;
        $this->storeManager = $storeManager;
        $this->cartManagement = $cartManagement;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        parent::__construct(
            $context,
            $customerSession,
            $helper,
            $customerAccountManagement,
            $resultJsonFactory,
            $resultRawFactory
        );
    }

    /**
     * Get account redirect.
     *
     * @return AccountRedirect|mixed
     */
    protected function getAccountRedirect()
    {
        if (!is_object($this->accountRedirect)) {
            $this->accountRedirect = ObjectManager::getInstance()->get(AccountRedirect::class);
        }
        return $this->accountRedirect;
    }

    /**
     * Initializes config dependency.
     *
     * @return ScopeConfigInterface|mixed
     */
    protected function getScopeConfig()
    {
        if (!is_object($this->scopeConfig)) {
            $this->scopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        }
        return $this->scopeConfig;
    }

     /**
      * Retrieve cookie manager
      *
      * @return PhpCookieManager|mixed
      */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                PhpCookieManager::class
            );
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
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    public function setCookie($key, $value, $metaData)
    {
        $this->getCookieManager()->setPublicCookie(
            $key,
            $value,
            $metaData
        );
    }

    /**
     * Login registered users and initiate a session.
     * Expects a POST. ex for JSON {"username":"user@magento.com", "password":"userpassword"}
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {
            $customer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );
            $customerToken = $this->customerTokenService->createCustomerAccessToken(
                $customer->getEmail(),
                $credentials['password']
            );
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
            if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') &&
                $redirectRoute) {
                $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                $this->getAccountRedirect()->clearRedirectCookie();
            }
            $customerEmail = base64_encode($customer->getEmail());
            $cookieDomain = $this->accountHelper->getCookieRedirect();
            $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setDomain($cookieDomain);
            $publicCookieMetadata->setHttpOnly(false);
            $publicCookieMetadata->setSecure(true);
            $publicCookieMetadata->setSameSite('Lax');
            $this->setCookie(
                'abt_usr',
                '{"email":"' . $customerEmail . '","token":"' .
                $customerToken . '","fname":"' . $customer->getFirstName()  . '","cgroup":"' 
                . base64_encode($customer->getGroupId()) . '"}',
                $publicCookieMetadata
            );
        } catch (LocalizedException $e) {
            $response = [
               'errors' => true,
               'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $response = [
               'errors' => true,
               'message' => __('Invalid login or password.'),
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * GetCartId function
     *
     * @param $customer
     * @return string
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    private function getCartId($customer)
    {
        $customerId = $customer->getId();
        $storeId = $this->storeManager->getStore()->getId();
        try {
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($customerId, null);
            $cart =  $this->cartManagement->getCartForCustomer($customerId);
        }
        if ($storeId == AccountHelper::GLU_STORE_ID || $storeId == AccountHelper::SIM_STORE_ID) {
            $cart->removeAllItems();
            $cart->collectTotals()->save();
            if ($storeId == AccountHelper::GLU_STORE_ID) {
                $this->checkoutSession->loadCustomerQuote();
            }
        }
        $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        if (empty($maskedId)) {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId((int) $cart->getId());
            $this->quoteIdMaskResourceModel->save($quoteIdMask);
            $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        }
        return $maskedId;
    }
}
