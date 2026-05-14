<?php

namespace Abbott\OneTrust\Helper;

use Abbott\OneTrust\Logger\Logger;
use Abbott\OneTrust\Model\Api\ApiInterface;
use Abbott\OneTrust\Model\OneTrust;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Api extends AbstractHelper
{
    /**
     * @var scopeConfig
     */
    protected $scopeConfig;

    /**
     * @var serializer
     */
    protected $serializer;

    /**
     * @var cache
     */
    protected $cache;

    /**
     * @var newsLetterPurposeId
     */
    protected $newsLetterPurposeId;

    /**
     * @var empPurposeId
     */
    protected $empPurposeId;

    /**
     * @var token
     */
    protected $token;

    /**
     * @var registrationCollectionPointsId
     */

    private $registrationCollectionPointsId;

    /**
     * @var checkoutCollectionPointId
     */

    private $checkoutCollectionPointId;

    /**
     * @var endPointURL
     */
    private $endPointURL;

    /**
     * Logging instance
     *
     * @var $logger
     */
    protected $logger;

    /**
     * @var jwtToken
     */
    protected $jwtToken;

    /**
     * @var mboJwtToken
     */
    protected $mboJwtToken;

    /**
     * @var checkoutJwtToken
     */
    protected $checkoutJwtToken;

    /**
     * @var postConsentsEndPointURL
     */
    protected $postConsentsEndPointURL;

    /**
     *
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var OneTrust
     */
    protected $oneTrust;

    /**
     * Construct function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     * @param Logger $logger
     * @param CurlFactory $curlFactory
     * @param EncryptorInterface $encryptor
     * @param OneTrust $oneTrust
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        CacheInterface $cache,
        Logger $logger,
        CurlFactory $curlFactory,
        EncryptorInterface $encryptor,
        OneTrust $oneTrust
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->setApiEndPoint();
        $this->logger = $logger;
        $this->curlFactory = $curlFactory;
        $this->encryptor = $encryptor;
        $this->oneTrust = $oneTrust;
    }

    /**
     * Set endpoint and collection point id
     *
     * @return void
     */
    private function setApiEndPoint(): void
    {
        $this->registrationCollectionPointsId = $this->getConfig(ApiInterface::REGISTRATION_COLLECTION_POINTS_ID);
        $this->checkoutCollectionPointId = $this->getConfig(ApiInterface::CHECKOUT_COLLECTION_POINT_ID);
        $this->endPointURL = $this->getConfig(ApiInterface::COLLECTION_POINT_URL);
        $this->newsLetterPurposeId = $this->getConfig(ApiInterface::NEWSLETTER_PURPOSE_ID);
        $this->empPurposeId = $this->getConfig(ApiInterface::CHECKOUT_EMP_PURPOSE_ID);
        $this->postConsentsEndPointURL = $this->getConfig(ApiInterface::POST_CONSENT_POINT_URL);
    }

    /**
     * Post consents to ONE Trust API call
     *
     * @param string $customerEmail
     * @param array $actionArray
     * @param string $pageType
     * @return mixed
     */
    public function postConsentToOneTrust($customerEmail, $actionArray, $pageType): mixed
    {
        try {
            $curl = $this->curlFactory->create();
            $curl->addHeader(ApiInterface::CONTENT_TYPE, ApiInterface::APPLICATION_JSON);
            $apiUrl = $this->postConsentsEndPointURL;

            $this->logger->info('==== Post API URL ====');
            $this->logger->info($apiUrl);

            $bodyContent = $this->prepareBody($customerEmail, $actionArray, $pageType);
            $curl->post($apiUrl, $bodyContent);

            $this->logger->info('==== Post API Request Data ====');
            $this->logger->info(print_r($bodyContent, true));

            $response = $curl->getBody();
            $response = $this->serializer->unserialize($curl->getBody());
            $response[ApiInterface::STATUS] = $curl->getStatus();

            $this->logger->info('==== Post API Response Data ====');
            $this->logger->info(print_r($response, true));
        } catch (\Exception $exception) {
            $response[ApiInterface::STATUS] = $curl->getStatus();
            $response[ApiInterface::REASON] = $exception->getMessage();
            $this->logger->info('==== Post API Response Data ====');
            $this->logger->info(print_r($response, true));
        }
        return $response;
    }

    /**
     * Get Config Value
     *
     * @param string $configPath
     * @return mixed
     */
    private function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check Module is Enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfig(ApiInterface::MODULE_ENABLED);
    }

    /**
     * Prepare Body For Post Consents
     *
     * @param string $customerEmail
     * @param array $actionArray
     * @param string $pageType
     * @return false|string
     */
    private function prepareBody($customerEmail, $actionArray, $pageType)
    {
        $purposeArray = [];
        if ($pageType == 'CUSTOMER_REGISTRATION') {
            $purposeArray = $this->prepareCustomerRegistrationPurposeData($actionArray);
            $registrationCollectionPointId = $this->getConfig(ApiInterface::REGISTRATION_COLLECTION_POINTS_ID);
            $response = $this->oneTrust->getCollectionPointDetails($registrationCollectionPointId);
            $this->jwtToken = $response['content'][0]['jwtToken']['token'];
        }
        if ($pageType == 'MBO_CUSTOMER_UPDATE') {
            $purposeArray = $this->prepareMboCustomerUpdatePurposeData($actionArray);
            $mboNewsLetterCollectionPointId = $this->getConfig(Data::XML_PATH_MBO_COLLECTION_POINT_ID);
            $response = $this->oneTrust->getCollectionPointDetails($mboNewsLetterCollectionPointId);
            $this->jwtToken = $response['content'][0]['jwtToken']['token'];
        }
        if ($pageType == 'CHECKOUT_CONSENT') {
            $purposeArray = $this->prepareCheckoutConsentPurposeData($actionArray);
            $checkoutCollectionPointId = $this->getConfig(ApiInterface::CHECKOUT_COLLECTION_POINT_ID);
            $response = $this->oneTrust->getCollectionPointDetails($checkoutCollectionPointId);
            $this->jwtToken = $response['content'][0]['jwtToken']['token'];
        }
        if ($pageType == 'MBO_CHECKOUT_CONSENT') {
            $purposeArray = $this->prepareMboCheckoutConsentPurposeData($actionArray);
            $mboCheckoutCollectionPointId = $this->getConfig(Data::XML_PATH_MBO_CHECKOUT_COLLECTION_POINT_ID);
            $response = $this->oneTrust->getCollectionPointDetails($mboCheckoutCollectionPointId);
            $this->jwtToken = $response['content'][0]['jwtToken']['token'];
        }

        $payload = [
            ApiInterface::IDENTIFIER => $customerEmail,
            ApiInterface::REQUEST_INFO => $this->jwtToken,
            ApiInterface::PURPOSE => $purposeArray
        ];

        return json_encode($payload);
    }

    /**
     * Prepare Purpose Array For Post Consents
     *
     * @param string $purposeId
     * @param mixed $value
     * @return array
     */
    private function preparePurposeArray($purposeId, $value)
    {
        if ($value == 1) {
            $status = ApiInterface::CONFIRMED;
        } elseif ($value == 0) {
            $status = ApiInterface::WITHDRAWN;
        } else {
            $status = ApiInterface::NOTGIVEN;
        }

        // Add Purpose id array as per the selected checkbox
        return [
            ApiInterface::ID => $purposeId,
            ApiInterface::TRANSACTION_TYPE => $status
        ];
    }

    /**
     * PrepareCustomerRegistrationPurposeData function
     *
     * @param array $actionArray
     * @return array
     */
    private function prepareCustomerRegistrationPurposeData($actionArray): array
    {
        $purposeArray = [];
        if (isset($actionArray)) {
            foreach ($actionArray as $key => $value) {
                if ($key == 'newsletter_subscriber') {
                    $newsLetterPurposeIdFe = $this->getConfig(ApiInterface::NEWSLETTER_PURPOSE_ID);
                    $purposeArray[] = $this->preparePurposeArray($newsLetterPurposeIdFe, $value);
                }
            }
        }
        return $purposeArray;
    }

    /**
     * PrepareMboCustomerUpdatePurposeData function
     *
     * @param array $actionArray
     * @return array
     */
    private function prepareMboCustomerUpdatePurposeData($actionArray): array
    {
        $purposeArray = [];
        if (isset($actionArray)) {
            foreach ($actionArray as $key => $value) {
                if ($key == 'mbo_newsletter_subscriber') {
                    $mboNewsLetterPurposeId = $this->getConfig(ApiInterface::MBO_NEWSLETTER_PURPOSE_ID);
                    $purposeArray[] = $this->preparePurposeArray($mboNewsLetterPurposeId, $value);
                }
            }
        }
        return $purposeArray;
    }

    /**
     * PrepareCheckoutConsentPurposeData function
     *
     * @param array $actionArray
     * @return array
     */
    private function prepareCheckoutConsentPurposeData($actionArray): array
    {
        $purposeArray = [];
        if (isset($actionArray)) {
            foreach ($actionArray as $key => $value) {
                if ($key == 'checkout_employee_consent' && $value) {
                    $checkoutEmpPurposeId = $this->getConfig(ApiInterface::CHECKOUT_EMP_PURPOSE_ID);
                    $purposeArray[] = $this->preparePurposeArray($checkoutEmpPurposeId, $value);
                }
                if ($key == 'checkout_payment_consent') {
                    $checkoutPaymentPurposeId = $this->getConfig(ApiInterface::CHECKOUT_PAYMENT_PURPOSE_ID);
                    $purposeArray[] = $this->preparePurposeArray($checkoutPaymentPurposeId, $value);
                }
            }
        }
        return $purposeArray;
    }

    /**
     * PrepareMBOCheckoutConsentPurposeData function
     *
     * @param array $actionArray
     * @return array
     */
    private function prepareMboCheckoutConsentPurposeData($actionArray): array
    {
        $purposeArray = [];
        if (isset($actionArray)) {
            foreach ($actionArray as $key => $value) {
                if ($key == 'checkout_employee_consent' && $value) {
                    $mboCheckoutEmpPurposeId = $this->getConfig(Data::XML_PATH_MBO_CHECKOUT_EMP_PURPOSE_ID);
                    $purposeArray[] = $this->preparePurposeArray($mboCheckoutEmpPurposeId, $value);
                }
            }
        }
        return $purposeArray;
    }
}
