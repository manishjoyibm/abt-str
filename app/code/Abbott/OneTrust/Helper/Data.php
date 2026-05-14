<?php

declare(strict_types=1);

namespace Abbott\OneTrust\Helper;

use Abbott\OneTrust\Logger\Logger;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;

class Data extends AbstractHelper
{
    public const XML_PATH_MODULE_STATUS = 'onetrust/general/enabled';
    public const XML_PATH_CLIENT_ID = 'onetrust/general/client_id';
    public const XML_PATH_CLIENT_SECRET = 'onetrust/general/client_secret';
    public const XML_PATH_OAUTH_URL = 'onetrust/general/oauth_token_request_url';
    private const XML_PATH_OAUTH_TOKEN = 'onetrust/general/oauth_token';
    public const XML_PATH_PREFERENCE_CENTER_ENDPOINT = 'onetrust/preference_center/preference_center_endpoint';
    public const XML_PATH_PREFERENCE_CENTER_ID = 'onetrust/preference_center/preference_center_id';
    public const XML_PATH_DATA_SUBJECT_ENDPOINT = 'onetrust/preference_center/data_subject_endpoint';
    public const XML_PATH_COLLECTION_POINT_URL = 'onetrust/general/get_collection_end_point';
    public const XML_PATH_MBO_COLLECTION_POINT_ID = 'onetrust/mbo_environment_configuration/mbo_collection_point_id';
    public const XML_PATH_MBO_JWT_TOKEN = 'onetrust/mbo_environment_configuration/mbo_jwt_token';
    public const XML_PATH_SDK_ENDPOINT = 'onetrust/consent_sdk/sdk_endpoint';
    public const XML_PATH_SDK_SETTING = 'onetrust/consent_sdk/sdk_setting';
    public const XML_PATH_SDK_STORAGE_CONTAINER = 'onetrust/consent_sdk/sdk_storage_container';
    public const XML_PATH_SDK_JS_URL = 'onetrust/consent_sdk/sdk_js_url';
    public const XML_PATH_NEWSLETTER_NOTICE_ID = 'onetrust/environment_configuration/newsletter_notice_id';
    public const XML_PATH_CHECKOUT_EMP_NOTICE_ID = 'onetrust/checkout_environment_configuration/emp_consent_notice_id';
    public const XML_PATH_CHECKOUT_PAYMENT_NOTICE_ID =
        'onetrust/checkout_environment_configuration/payment_consent_notice_id';
    public const XML_PATH_CREATE_DATA_SUBJECT_ENDPOINT = 'onetrust/general/create_data_subject_endpoint';
    public const XML_PATH_MBO_CHECKOUT_COLLECTION_POINT_ID = 'onetrust/mbo_checkout_configuration/collection_point_id';
    public const XML_PATH_MBO_CHECKOUT_EMP_PURPOSE_ID = 'onetrust/mbo_checkout_configuration/employee_term';
     public const XML_PATH_CHECKOUT_SUBSCRIPTION_NOTICE_ID =
        'onetrust/checkout_environment_configuration/subscription_consent_notice_id';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param Logger $logger
     * @param Curl $curl
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        EncryptorInterface $encryptor,
        Logger $logger,
        Curl $curl
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->curl = $curl;
        parent::__construct($context);
    }

    /**
     * Get Config Value
     *
     * @param string $configPath
     * @param string $scope
     * @param int $scopeId
     * @return string|null
     */
    public function getConfigValue($configPath, $scope = null, $scopeId = null): string|null
    {
        return $scopeId ?
            $this->scopeConfig->getValue($configPath, $scope, $scopeId)
            : $this->scopeConfig->getValue($configPath);
    }

    /**
     * Get Client ID
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->encryptor->decrypt($this->getConfigValue(self::XML_PATH_CLIENT_ID));
    }

    /**
     * Get Client Secret
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->encryptor->decrypt($this->getConfigValue(self::XML_PATH_CLIENT_SECRET));
    }

    /**
     * Get OAuth Request Endpoint
     *
     * @return string
     */
    public function getOauthEndpoint(): string
    {
        return $this->getConfigValue(self::XML_PATH_OAUTH_URL);
    }

    /**
     * Get Preference Center Endpoint
     *
     * @return string|null
     */
    public function getPreferenceCenterEndpoint(): string|null
    {
        return $this->getConfigValue(self::XML_PATH_PREFERENCE_CENTER_ENDPOINT);
    }

    /**
     * Get Preference Center ID
     *
     * @return string|null
     */
    public function getPreferenceCenterId(): string|null
    {
        return $this->getConfigValue(self::XML_PATH_PREFERENCE_CENTER_ID);
    }

    /**
     * Get Data Subject Endpoint
     *
     * @return string|null
     */
    public function getDataSubjectEndpoint(): string|null
    {
        return $this->getConfigValue(self::XML_PATH_DATA_SUBJECT_ENDPOINT);
    }

    /**
     * Get OAuth Token
     *
     * @return string
     */
    public function getOauthToken(): string
    {
        return $this->encryptor->decrypt($this->getConfigValue(self::XML_PATH_OAUTH_TOKEN));
    }

    /**
     * Check Module is Enabled
     *
     * @param string $scope
     * @param int $scopeId
     * @return bool
     */
    public function isModuleEnabled($scope = null, $scopeId = null): bool
    {
        return (bool)$this->getConfigValue(self::XML_PATH_MODULE_STATUS, $scope, $scopeId);
    }

    /**
     * Get Collection Point Details Endpoint
     *
     * @return string|null
     */
    public function getCollectionPointDetailsEndpoint(): string|null
    {
        return $this->getConfigValue(self::XML_PATH_COLLECTION_POINT_URL);
    }

    /**
     * Get MBO Collection Point ID
     *
     * @return string|null
     */
    public function getMboCollectionPointId(): string|null
    {
        return $this->getConfigValue(self::XML_PATH_MBO_COLLECTION_POINT_ID);
    }

    /**
     * Get SDK endpoint value
     *
     * @return string|null
     */
    public function getSdkEndpoint(): ?string
    {
        return $this->getConfigValue(self::XML_PATH_SDK_ENDPOINT);
    }

    /**
     * Get Storage parameter value
     *
     * @return string|null
     */
    public function getSdkSetting(): ?string
    {
        return $this->encryptor->decrypt($this->getConfigValue(self::XML_PATH_SDK_SETTING));
    }

    /**
     * Get Storage container value
     *
     * @return string|null
     */
    public function getSdkStorageContainer(): ?string
    {
        return $this->getConfigValue(self::XML_PATH_SDK_STORAGE_CONTAINER);
    }

    /**
     * Get SDK Js URL
     *
     * @return string|null
     */
    public function getSdkJsUrl(): ?string
    {
        return $this->getConfigValue(self::XML_PATH_SDK_JS_URL);
    }

    /**
     * Get notice id value
     *
     * @param string $type
     * @return string
     */
    public function getNoticeId($type): string
    {
        $noticeId = '';
        if ($type == 'newsletter_notice') {
            $noticeId = $this->getConfigValue(self::XML_PATH_NEWSLETTER_NOTICE_ID);
        } elseif ($type == 'employee_notice') {
            $noticeId = $this->getConfigValue(self::XML_PATH_CHECKOUT_EMP_NOTICE_ID);
        } elseif ($type == 'payment_term_notice') {
            $noticeId = $this->getConfigValue(self::XML_PATH_CHECKOUT_PAYMENT_NOTICE_ID);
        } elseif ($type == 'subscription_term_notice') { // this id will be provide by onetrust
            $noticeId = $this->getConfigValue(self::XML_PATH_CHECKOUT_SUBSCRIPTION_NOTICE_ID);
        }
        return $noticeId;
    }

    /**
     * Get Create Data Subject Endpoint
     *
     * @return string|null
     */
    public function getCreateDataSubjectEndpoint(): ?string
    {
        return $this->getConfigValue(self::XML_PATH_CREATE_DATA_SUBJECT_ENDPOINT);
    }

    /**
     * Check if SDK JS is Available or Down
     *
     * @return bool
     */
    public function isSdkJsAvailable(): bool
    {
        try {
            $curl = new($this->curl);
            $curl->get($this->getSdkJsUrl());
            $status = $curl->getStatus();
            $this->logger->info('OneTrust SDK JS Response Code = ' . $status);
            if ($status == 200) {
                return true;
            }
        } catch (Exception $exception) {
            $this->logger->info('OneTrust SDK JS Response Code = ' . $exception->getCode());
            $this->logger->info($exception->getMessage());
        }
        return false;
    }
}
