<?php

declare(strict_types=1);

namespace Abbott\OneTrust\Model;

use Abbott\OneTrust\Helper\Data;
use Abbott\OneTrust\Logger\Logger;
use Abbott\OneTrust\Model\Api\ApiInterface;
use Exception;
use Magento\Framework\App\Cache\TypeListInterface as Cache;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * OneTrust class to call OneTrust APIs
 */
class OneTrust
{
    private const XML_PATH_OAUTH_TOKEN = 'onetrust/general/oauth_token';

    public const BEARER = "Bearer ";

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Logger $logger
     * @param Curl $curl
     * @param SerializerInterface $serializer
     * @param Data $helper
     * @param WriterInterface $configWriter
     * @param Cache $cache
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Logger $logger,
        Curl $curl,
        SerializerInterface $serializer,
        Data $helper,
        WriterInterface $configWriter,
        Cache $cache,
        EncryptorInterface $encryptor
    ) {
        $this->logger = $logger;
        $this->curl = $curl;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->configWriter = $configWriter;
        $this->cache = $cache;
        $this->encryptor = $encryptor;
    }

    /**
     * Generate OAuth Token of OneTrust
     *
     * @return mixed|string
     */
    public function generateOauthToken(): mixed
    {
        $oAuthToken = null;
        if ($this->helper->isModuleEnabled()) {
            $clientId = $this->helper->getClientId();
            $clientSecret = $this->helper->getClientSecret();
            $oAuthUrl = $this->helper->getOauthEndpoint();

            if (empty($clientId) || empty($clientSecret) || empty($oAuthUrl)) {
                $this->logger->critical('Required Values are not specified');
                return $this;
            }

            $this->curl->addHeader("Content-Type", "application/x-www-form-urlencoded");

            $postParams = [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials'
            ];

            try {
                $this->curl->post($oAuthUrl, $postParams);
                $responseBody = $this->curl->getBody();
                if (!empty($responseBody)) {
                    $response = $this->serializer->unserialize($responseBody);
                    $oAuthToken = $response['access_token'];
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $oAuthToken;
    }

    /**
     * Get Preference Center Url
     *
     * @param string $email
     * @return string|null
     */
    public function getPreferenceCenterUrl(string $email): string|null
    {
        if ($this->helper->isModuleEnabled()) {
            $urlPrefix = $this->helper->getPreferenceCenterEndpoint();
            $preferenceCenterId = $this->helper->getPreferenceCenterId();
            if (!empty($email) || !empty($urlPrefix) || !empty($preferenceCenterId)) {
                $customerToken = $this->generateCustomerLinkToken($email);
                return $urlPrefix . $preferenceCenterId . "/" . $customerToken;
            }
        }
        return null;
    }

    /**
     * Generate Customer Link Token for Preference Center
     *
     * @param string $email
     * @param string|null $oAuthToken
     * @return string|null
     */
    private function generateCustomerLinkToken(string $email, string $oAuthToken = null): string|null
    {
        $customerLinkToken = null;
        if ($this->helper->isModuleEnabled()) {
            $dataSubjectEndpoint = $this->helper->getDataSubjectEndpoint();
            $this->logger->info('generateCustomerLinkToken Details');
            $this->logger->info($dataSubjectEndpoint);
            if (empty($oAuthToken)) {
                $oAuthToken = $this->helper->getOauthToken();
            }

            $curl = new($this->curl);
            $curl->addHeader("Authorization", self::BEARER . $oAuthToken);
            $curl->addHeader("identifier", $email);

            try {
                $curl->get($dataSubjectEndpoint);
                $status = $curl->getStatus();
                $responseBody = $curl->getBody();
                if ($status == 200 && !empty($responseBody)) {
                    $response = $this->serializer->unserialize($responseBody);
                    $this->logger->info(print_r($response, true));
                    $customerLinkToken = urlencode($response['linkToken']);
                } elseif ($status == 404) {
                    //create customer in OneTrust and resend the request
                    $this->createCustomerInOneTrust($email);
                    $customerLinkToken = $this->generateCustomerLinkToken($email);
                } elseif ($status == 401) {
                    //regenerate oauth token and resend the request
                    $oAuthToken = $this->generateOauthToken();
                    $customerLinkToken = $this->generateCustomerLinkToken($email, $oAuthToken);
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $customerLinkToken;
    }

    /**
     * Generate OAuth Token of OneTrust and store it in Config
     *
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    public function generateOauthTokenAndStoreInConfig($scope = null, $scopeId = null): void
    {
        $clientId = $this->helper->getClientId();
        $clientSecret = $this->helper->getClientSecret();
        $oAuthUrl = $this->helper->getOauthEndpoint();

        if (empty($clientId) || empty($clientSecret) || empty($oAuthUrl)) {
            $this->logger->critical('Required Values are not specified');
        }
        $curl = new($this->curl);
        $curl->addHeader("Content-Type", "application/x-www-form-urlencoded");

        $postParams = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials'
        ];

        try {
            $curl->post($oAuthUrl, $postParams);
            $responseBody = $curl->getBody();
            if (!empty($responseBody)) {
                $response = $this->serializer->unserialize($responseBody);
                $accessToken = $this->encryptor->encrypt($response['access_token']);
                $this->configWriter->save(self::XML_PATH_OAUTH_TOKEN, $accessToken, $scope, $scopeId);
                $this->cache->cleanType('config');
                $this->logger->info('OneTrust OAuth Token Generated Successfully.');
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Get Collection Point Details
     *
     * @param int $collectionPointId
     * @param string $oAuthToken
     * @return mixed
     */
    public function getCollectionPointDetails($collectionPointId, $oAuthToken = null): mixed
    {
        $responseData = null;
        $this->logger->info('Get Collection Point Details');
        if (empty($oAuthToken)) {
            $oAuthToken = $this->helper->getOauthToken();
        }
        $endpoint = $this->helper->getCollectionPointDetailsEndpoint();

        $curl = new($this->curl);
        $curl->addHeader(ApiInterface::ACCEPT, ApiInterface::APPLICATION_JSON);
        $curl->addHeader("Authorization", self::BEARER . $oAuthToken);
        $curl->addHeader("Content-Type", "application/json");

        $queryParams = [
            'id' => $collectionPointId,
            'status' => 'ACTIVE'
        ];
        $url = $endpoint . '?' . http_build_query($queryParams);
        $this->logger->info('GET URL : ' . $url);

        try {
            $curl->get($url);
            $status = $curl->getStatus();
            $responseBody = $curl->getBody();
            if ($status == 200 && !empty($responseBody)) {
                $responseData = $this->serializer->unserialize($responseBody);
                $this->logger->info('Response : ' . $responseBody);
            } elseif ($status == 401) {
                //regenerate oauth token and resend the request
                $oAuthToken = $this->generateOauthToken();
                $responseData = $this->getCollectionPointDetails($collectionPointId, $oAuthToken);
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return $responseData;
    }

    /**
     * Create Customer In OneTrust
     *
     * @param string $email
     * @param string|null $oAuthToken
     * @return void
     */
    public function createCustomerInOneTrust($email, $oAuthToken = null): void
    {
        if (empty($oAuthToken)) {
            $oAuthToken = $this->helper->getOauthToken();
        }

        $endpoint = $this->helper->getCreateDataSubjectEndpoint();

        $curl = new($this->curl);
        $curl->addHeader(ApiInterface::ACCEPT, ApiInterface::APPLICATION_JSON);
        $curl->addHeader("Authorization", self::BEARER . $oAuthToken);
        $curl->addHeader("Content-Type", "application/json");
        $curl->addHeader("dataSubjectIdentifier", $email);

        $postParams = [
            'identifierType' => 'Email',
            'language' => 'en_us'
        ];
        $postParams = $this->serializer->serialize($postParams);

        $this->logger->info('Create Data Subject Endpoint : ' . $endpoint);
        try {
            $curl->post($endpoint, $postParams);
            $status = $curl->getStatus();
            $responseBody = $curl->getBody();
            if ($status == 200 && !empty($responseBody)) {
                $this->serializer->unserialize($responseBody);
                $this->logger->info('Response : ' . $responseBody);
            } elseif ($status == 401) {
                //regenerate oauth token and resend the request
                $oAuthToken = $this->generateOauthToken();
                $this->createCustomerInOneTrust($email, $oAuthToken);
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
