<?php
namespace Abbott\CustomerTwoFactorAuth\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Abbott\CustomerTwoFactorAuth\Model\OtpFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_MODULE_ENABLE = "customertwofactorauth/general/enable";
    public const XML_PATH_STORE_NAME = 'trans_email/ident_sales/name';
    public const XML_PATH_STORE_EMAIL = 'trans_email/ident_sales/email';
    public const XML_PATH_OTP_TEMPLATE = 'customertwofactorauth/general/template';
    public const IS_ENABLE = 'allow_2FA';
    public const XML_PATH_MAX_OTP = 'customertwofactorauth/general/otp_limit';
    public const XML_PATH_EXPIRY_LIMIT= 'customertwofactorauth/general/otp_expiry';
    public const ENABLING_2FA = '2fa_enabling';
    public const XML_PATH_LOCKED_UNTIL= 'customertwofactorauth/general/locked_until';
    /**
     * @var CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * @var OtpFactory
     */
    public $otpFactory;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param OtpFactory $otpFactory
     * @param RemoteAddress $remoteAddress
     */

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        OtpFactory $otpFactory,
        RemoteAddress $remoteAddress,
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->otpFactory = $otpFactory;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * check if the customer security feature enable
     * @param $email
     * @return bool
     */
    public function isCustomerSecurityEnabled($email = null)
    {
        if ($this->customerSession->getCustomer()->getId()) {
            $customerId= $this->customerSession->getCustomer()->getId();
            $customer = $this->customerRepository->getById($customerId);
        } else {
            $customer = $this->customerRepository->get($email);
        }
        $isEnable = $customer->getCustomAttribute(self::IS_ENABLE);
        return $isEnable !== null ? (bool) $isEnable->getValue() : false;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setCustomerSecurity()
    {
        $customer = $this->customerRepository->getById($this->customerSession->getCustomer()->getId());
        $customer->setCustomAttribute(self::IS_ENABLE, 1);
        return $this->customerRepository->save($customer);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerData()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * check if module enabled
     * @return mixed
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MODULE_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getStoreName($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STORE_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getStoreEmail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STORE_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_OTP_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getOtpLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_OTP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getExpiryLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EXPIRY_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Client IP Address
     * @return string
     */
    public function getClientIpAddress()
    {
       
        $ip = $this->remoteAddress->getRemoteAddress();
        $ipList = explode(',', $ip);
        return trim($ipList[0]); // First IP is usually the client I

    }

    /**
     * @param $store
     * @return mixed
     */
    public function getLockedUntill($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOCKED_UNTIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
