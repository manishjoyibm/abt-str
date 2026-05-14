<?php

namespace Abbott\MyAccount\Helper;

use Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\Config;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Integration\Model\CustomerTokenService;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\Escaper;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Helper class for My Account:Contact Preferences
 */
class Data extends AbstractHelper
{
    public $_storeManager;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    public $createEmptyCartForCustomer;
    public $cartManagement;
    public $quoteIdMaskFactory;
    public $quoteIdMaskResourceModel;
    public $quoteIdToMaskedQuoteId;
    public $productRepository;
    public $escaper;
    public $customerSession;
    public $collectionFactory;
    public const XML_PATH_MYACCOUNT = 'my_account/contact_preferences/';
    public const XML_PATH_MYACCOUNT_REDIRECT = 'my_account/redirect_settings/';
    public const XML_COOKIE_REDIRECT = 'web/cookie/cookie_domain';
    public const ABT_STORE_ID = 1;
    public const GLU_STORE_ID = 2;
    public const SIM_STORE_ID = 3;
    public const NEW_SIM_STORE_CODE = 'new_similac';
    public const PDL_STORE_CODE = 'pedialyte';
    public const XML_PATH_MYACCOUNT_DOMAIN_RESTRICTION = 'my_account/disposable_email_domain_restriction/';

    public const RESTRICTED_DOMAIN_LIST= 'restricted_domain_list';

    public const ENABLE_DOMAIN_RESTRICTION= 'enable_disposable_email_domain_restriction';

    public const ERROR_MESSAGE_DISPOSABLE_EMAIL_DOMAN = 'disposable_email_domain_error_message';

    protected $cookieMetadataFactory;

    protected $customerTokenService;

    protected $cookieManager;

    protected $sessionConfig;
    protected $tokenFactory;
    protected $storeRepository;
    /**
     * @var AddressMetadataInterface
     */

    private $addressMetadata;
    protected $logger;

    public const GOOGLE_ANALYTICS_ENABLE = 'my_account/googleanalytics/ga_enabled';

    public const XML_DISABLE_INLINE_EMAIL = 'my_account/customer_login/disable_inline_email';

    /**
     * Construct function
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CustomerTokenService $customerTokenService
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $cookieManager
     * @param Config $sessionConfig
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param CartManagementInterface $cartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param ProductRepositoryInterface $productRepository
     * @param TokenModelFactory $tokenFactory
     * @param AddressMetadataInterface $addressMetadata
     * @param Escaper $escaper
     * @param StoreRepositoryInterface $storeRepository
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CustomerTokenService $customerTokenService,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager,
        Config $sessionConfig,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        CartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ProductRepositoryInterface $productRepository,
        TokenModelFactory $tokenFactory,
        AddressMetadataInterface $addressMetadata,
        Escaper $escaper,
        StoreRepositoryInterface $storeRepository,
        Session $customerSession,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        $this->_storeManager = $storeManager;
        $this->customerTokenService = $customerTokenService;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->sessionConfig = $sessionConfig;
        $this->jsonHelper = $jsonHelper;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->productRepository = $productRepository;
        $this->addressMetadata = $addressMetadata;
        $this->escaper = $escaper;
        $this->tokenFactory = $tokenFactory;
        $this->storeRepository = $storeRepository;
        $this->customerSession = $customerSession;
        $this->collectionFactory =$collectionFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param $code
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfig($code)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MYACCOUNT . $code,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * GetConfigDomainRestriction function
     *
     * @param $code
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfigDomainRestriction($code)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MYACCOUNT_DOMAIN_RESTRICTION . $code,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * GetCookieRedirect function
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCookieRedirect()
    {
        return $this->scopeConfig->getValue(
            self::XML_COOKIE_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get the redirect configuration value
     *
     * @param $code
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getRedirectConfig($code)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MYACCOUNT_REDIRECT . $code,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * SetCookie function
     *
     * @param $key
     * @param $value
     * @param $metaData
     * @return void
     * @throws FailureToSendException
     * @throws InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     */
    public function setCookie($key, $value, $metaData)
    {
        $this->cookieManager->setPublicCookie(
            $key,
            $value,
            $metaData
        );
    }

    /**
     * SetAllCookies function
     *
     * @param CustomerInterface $customer
     * @param $password
     * @return string
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws AuthenticationException
     */
    public function setAllCookies(CustomerInterface $customer, $password)
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), $password);
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setDomain($this->getCookieRedirect());
        $publicCookieMetadata->setHttpOnly(false);
        $publicCookieMetadata->setSecure(true);
        $publicCookieMetadata->setSameSite('Lax');
        $this->setCookie(
            'abt_usr',
            '{"email":"'.base64_encode($customer->getEmail()).'","token":"' .
            $customerToken . '","fname":"' . $customer->getFirstName() . '",
            "cgroup":"' . base64_encode($customer->getGroupId()) . '"}',
            $publicCookieMetadata
        );
        $cartId = $this->getCartId($customer);
        $this->setCookie('abt_sesCartKey', $cartId, $publicCookieMetadata);
        return $cartId;
    }

    /**
     * SetAllStoreCookies function
     *
     * @param CustomerInterface $customer
     * @return string
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function setAllStoreCookies(CustomerInterface $customer)
    {
        $customerToken = $this->tokenFactory->create()->createCustomerToken($customer->getId())->getToken();
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setDomain($this->getCookieRedirect());
        $publicCookieMetadata->setHttpOnly(false);
        $publicCookieMetadata->setSecure(true);
        $publicCookieMetadata->setSameSite('Lax');
        $this->setCookie(
            'abt_usr',
            '{"email":"'.base64_encode($customer->getEmail()).'","token":"' . $customerToken .
            '","fname":"' . $customer->getFirstName() . '",
            "cgroup":"' . base64_encode($customer->getGroupId()) . '",
            "link_hide":{"returns":0},"magento_page":{"orders":0,
            "subscriptions":0}}',
            $publicCookieMetadata
        );
        $cartId = $this->getCartId($customer);
        $this->setCookie('abt_sesCartKey', $cartId, $publicCookieMetadata);
        $this->setCustomcookie();
        return $cartId;
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
    public function getCartId($customer)
    {
        $customerId = $customer->getId();
        $this->createEmptyCartForCustomer->execute($customerId, null);
        $cart =  $this->cartManagement->getCartForCustomer($customerId);
        $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        if (empty($maskedId)) {
                  $quoteIdMask = $this->quoteIdMaskFactory->create();
                  $quoteIdMask->setQuoteId((int) $cart->getId());
                  $this->quoteIdMaskResourceModel->save($quoteIdMask);
                  $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        }
                    return $maskedId;
    }

    /**
     * RemoveCookie function
     *
     * @param $cookieKey
     * @return void
     * @throws InputException
     * @throws FailureToSendException
     */
    public function removeCookie($cookieKey)
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $metadata->setPath('/');
        $metadata->setDomain($this->getCookieRedirect());
        $this->cookieManager->deleteCookie($cookieKey, $metadata);
    }

    /**
     * RemoveCookie function
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * GetAemUrl function
     *
     * @param int $productId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getAemUrl($productId)
    {
        $product = $this->productRepository->getById($productId);
        return $product->getData('aem_url');
    }

    /**
     * GetFrontendLabel function
     *
     * @param $attributeCode
     * @return array|string
     * @throws LocalizedException
     */
    public function getFrontendLabel($attributeCode)
    {
        try {
            $attribute =  $this->addressMetadata->getAttributeMetadata($attributeCode);
            $frontendLabel = $attribute->getFrontendLabel();
        } catch (NoSuchEntityException $e) {
            $frontendLabel = '';
        }
        return $this->escaper->escapeHtml(__($frontendLabel));
    }

    /**
     * Get New Similac store id By Store Code
     *
     * @return int|void
     */
    public function getNewSimilacStoreId()
    {
        try {
            $store = $this->storeRepository->get(self::NEW_SIM_STORE_CODE);
            return $store->getId();
        } catch (NoSuchEntityException $e) {
            $this->logger->info("NewSimilacStoreId Not Found".$e->getMessage());
        }
    }

    /**
     * Make sure email address is not from disposable email domain
     *
     * @param string $email
     * @return bool
     */
    public function verifyDomain($email)
    {
        $domainList = explode(',', $this->getConfigDomainRestriction(self::RESTRICTED_DOMAIN_LIST));
        $isEmail = false;
        if (in_array(substr(strrchr($email, "@"), 1), $domainList)) {
             $isEmail = true;
        }
        return $isEmail;
    }

    /**
     * GetGASession function
     *
     * @return array
     */
    public function getGASession()
    {
        $session['add'] = $this->customerSession->getAddsave();
        $session['edit'] = $this->customerSession->getEditsave();
        $session['billingedit'] = $this->customerSession->getEditbillingsave();
        $session['shipingedit'] = $this->customerSession->getEditshipingsave();
        return $session;
    }

    /**
     * UnsetSession function
     *
     * @return void
     */
    public function unsetSession()
    {
        $this->customerSession->unsAddsave();
        $this->customerSession->unsEditshipingsave();
        $this->customerSession->unsEditbillingsave();
        $this->customerSession->unsEditsave();
    }

    /**
     * GetGAreturnSession function
     *
     * @return mixed
     */
    public function getGAreturnSession()
    {
        return $this->customerSession->getReturnsave();
    }

    /**
     * UnsetGAreturnSession function
     *
     * @return mixed
     */
    public function unsetGAreturnSession()
    {
        return $this->customerSession->unsReturnsave();
    }

    /**
     * GetConfigGoogleAnalyticsEnable function
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfigGoogleAnalyticsEnable()
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_ANALYTICS_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

     /**
      * GetProductAttributeConfig function
      *
      * @return mixed
      * @throws NoSuchEntityException
      */
    public function getProductAttributeConfig()
    {
        return $this->scopeConfig->getValue(
            'my_account/product/product_attribute',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * SetCustomcookie function
     *
     * @return void
     */
    public function setCustomcookie()
    {
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setDomain($this->getCookieRedirect());
        $publicCookieMetadata->setHttpOnly(false);
        $publicCookieMetadata->setSecure(true);
        $publicCookieMetadata->setSameSite('Lax');
        $this->setCookie('abt_mage_sessid', 'yes', $publicCookieMetadata);
    }

     /**
      * GetCustomerProfiles function
      *
      * @return Collection
      */
    public function getCustomerProfiles()
    {
        $customerProfiles = $this->collectionFactory->create();
        $customerProfiles->addFieldToFilter('customer_id', ['eq' => $this->customerSession->getCustomerId()]);
        return $customerProfiles;
    }
    
    /**
     * Get Pedialyte store id By Store Code
     *
     * @return int|void
     */
    public function getPedialyteStoreId()
    {
        try {
            $store = $this->storeRepository->get(self::PDL_STORE_CODE);
            return $store->getId();
        } catch (NoSuchEntityException $e) {
            $this->logger->info("PedialyteStoreId Not Found".$e->getMessage());
        }
    }
}
