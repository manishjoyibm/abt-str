<?php

namespace Abbott\AwsLambda\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     * @var curl
     */
    protected $curl;

    /**
     *
     * @var store id of customer
     */
    protected $custStoreId;

    protected $encryptor;

    /**
     *
     * @var Magento\Customer\Api\CustomerRepositoryInterfac
     */
    protected $customerRepository;

    /**
     * check api is enable or not
     */
    const API_ENABLED = 'abbott_awslambda/api/enable';

    /**
     * check api debug is enable or not
     */
    const API_DEBUG = 'abbott_awslambda/api/enable_debug';

    /**
     * APP ID
     */
    const API_APPID = 'abbott_awslambda/api/appid';
    /**
     * UID
     */
    const API_UID = 'abbott_awslambda/api/uid';

    /**
     * Access Key
     */
    const API_ACCESSKEY = 'abbott_awslambda/api/accesskey';

    /**
     * api post url of profile info
     */
    const API_POST_URL = 'abbott_awslambda/api/posturl';

    /**
     * api post url of profile info
     */
    const API_CUSTOMER_CREATION = 'abbott_awslambda/api/enable_customer_creation';

    /**
     * api post url of profile info
     */
    const API_GET_PROFILE = 'abbott_awslambda/api/get_profile_url';

    /**
     * api post url of deactivate account
     */
    const API_DEACTIVATE_PROFILE = 'abbott_awslambda/api/deactivate_profile_url';

     /**
     * api post url of profile info
     */
    const X_ORIGIN_SECRET = 'abbott_awslambda/api/x_origin_secret';

    /**
     *
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
    }

    /**
     *
     * @param string $url
     * @param string $params
     * @param string $gigyaUid
     * @return array
     */
    public function postData($url, $params, $gigyaUid)
    {
        if (!empty($this->getUID()) && !empty($this->getAccessKey())) {
            $this->curl->setCredentials($this->getUID(), $this->getAccessKey());
            $this->curl = $this->setAwsHeader($this->curl);
            $this->curl->addHeader("x-application-access-key", $this->getAccessKey());
            $this->curl->addHeader("uid", $gigyaUid);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->post($url, $params);
            return $this->curl->getBody();
        }
    }

    /**
     * Check api is enable or not
     *
     * @return boolean
     */
    public function enabled()
    {
        return $this->scopeConfig->getValue(
            self::API_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * Check api debug mode is enable or not
     *
     * @return boolean
     */
    public function enabledDebug()
    {
        return $this->scopeConfig->getValue(
            self::API_DEBUG,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * Get Application Id
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->scopeConfig->getValue(
            self::API_APPID,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * Get Uid
     *
     * @return string
     */
    public function getUID()
    {
        return $this->scopeConfig->getValue(
            self::API_UID,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * @return string
     */
    public function getAccessKey()
    {
        return $this->encryptor->decrypt(
            $this->scopeConfig->getValue(
                self::API_ACCESSKEY,
                ScopeInterface::SCOPE_STORES,
                $this->custStoreId
            )
        );
    }

    /**
     * @return string
     */
    public function getPostUrl()
    {
        return $this->scopeConfig->getValue(
            self::API_POST_URL,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * Set store id
     * @param type $storeId
     * @return type
     */
    public function setStoreId($storeId)
    {
        return $this->custStoreId = $storeId;
    }

    /**
     * @return integer
     */
    public function isCreateCustomerEnabled()
    {
        return $this->scopeConfig->getValue(
            self::API_CUSTOMER_CREATION,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * @return string
     */
    public function getProfileApiUrl()
    {
        return $this->scopeConfig->getValue(
            self::API_GET_PROFILE,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * @return string
     */
    public function getDeactivateProfileApiUrl()
    {
        return $this->scopeConfig->getValue(
            self::API_DEACTIVATE_PROFILE,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * @return string
     */
    public function getApppOriginSecret()
    {
        return $this->scopeConfig->getValue(
            self::X_ORIGIN_SECRET,
            ScopeInterface::SCOPE_STORES,
            $this->custStoreId
        );
    }

    /**
     * @param string
     * @param array
     * @return string
     */
    public function getProfile($token, $params = [])
    {
        if (!empty($this->getProfileApiUrl()) && !empty($this->getAppId()) && !empty($token)) {
            $this->curl = $this->setAwsHeader($this->curl);
            $this->curl->addHeader("x-id-token", $token);
            $this->curl->get($this->getProfileApiUrl());
            return $this->curl->getBody();
        }
    }

    /**
     * @param array
     * @return string
     */
    public function deactivateProfile($params)
    {
        $url = $this->getDeactivateProfileApiUrl();
        if (!empty($url) && !empty($this->getAppId()) && !empty($params)) {
            $this->curl = $this->setAwsHeader($this->curl);
            $this->curl->addHeader("x-application-access-key", $this->getAccessKey());
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, "DELETE");
            $this->curl->post($url, $params);
            return $this->curl->getBody();
        }
    }

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @return void|\Magento\Customer\Model\Session
     */
    public function initiate($customerSession, $urlInterface, $cart = true)
    {
        if ($customerSession->getStopCreateCustomer() == 1) {
            if (!$cart) {
                $redirectionUrl = $urlInterface->getUrl('/checkout/cart/');
                return $customerSession->authenticate($redirectionUrl);
            }
            $customerSession->setStopCreateCustomer(0);
            return;
        }
        $customerSession->setAfterAuthUrl($urlInterface->getCurrentUrl());
        $redirectionUrl = $urlInterface->getUrl('delaycustomer/customer/create');
        return $customerSession->authenticate($redirectionUrl);
    }

    /**
     * @param curl
     * @return object
     */
    public function setAwsHeader($curl)
    {
            $curl->addHeader("Content-Type", "application/json");
            $curl->addHeader("x-country-code", "US");
            $curl->addHeader("x-application-id", $this->getAppId());
            $curl->addHeader("x-preferred-language", "en-US");
            $curl->addHeader("x-origin-secret", $this->getApppOriginSecret());
            $curl->setOption(CURLOPT_RETURNTRANSFER, true);
            return $curl;
    }

    /**
     * Get gigya uid as per customerid
     * @param type $customerId
     * @return boolean
     */
    public function getGigyaUid($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getCustomAttribute('gigya_uid')) {
            return $customer->getCustomAttribute('gigya_uid')->getValue();
        } else {
            return false;
        }
    }
}
