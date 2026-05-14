<?php

namespace Abbott\FedexTracking\Helper;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\CurlFactory;

class Data
{
    public const XML_FEDEX_TRACKING_TOKEN_API_URL = "fedex_tracking/fedex_tracking_configuration/api_url";
    public const XML_FEDEX_TRACKING_TRACK_API = "fedex_tracking/fedex_tracking_configuration/track_api_url";
    public const XML_FEDEX_TRACKING_USERNAME = "fedex_tracking/fedex_tracking_configuration/username";
    public const XML_FEDEX_TRACKING_PASSWORD = "fedex_tracking/fedex_tracking_configuration/password";

    /**
     * @var ShipmentRepositoryInterface
     */
    private ShipmentRepositoryInterface $shipmentRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $enc;

    /**
     * @var Curl
     */
    protected Curl $curl;

     /**
      * @var CurlFactory
      */
    private CurlFactory $curlFactory;

    /**
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Curl $curl
     * @param LoggerInterface $logger
     * @param EncryptorInterface $enc
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        LoggerInterface $logger,
        EncryptorInterface $enc,
        CurlFactory $curlFactory
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->logger = $logger;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->enc = $enc;
        $this->curlFactory = $curlFactory;
    }

    /**
     * Send tracking Info to paypal
     *
     * @param array $trackers
     * @return void
     */
    public function sendTrackingInfo(array $trackers): void
    {
        $accessToken = $this->getBearerToken();
        $url = $this->getConfigValue(self::XML_FEDEX_TRACKING_TRACK_API);
        try {
            $curl = $this->curlFactory->create();
            $curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $curl->addHeader("Content-Type", "application/json");
            $curl->addHeader("Authorization", "Bearer " . $accessToken);
            $curl->post($url, json_encode($trackers));
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * To Fetch bearer token from PayPal
     *
     * @return mixed|void
     */
    public function getBearerToken()
    {
        $tokenApi = $this->getConfigValue(self::XML_FEDEX_TRACKING_TOKEN_API_URL);
        $username = $this->getConfigValue(self::XML_FEDEX_TRACKING_USERNAME);
        $password = $this->getConfigValue(self::XML_FEDEX_TRACKING_PASSWORD);
        $password = $this->enc->decrypt($password);
        $data = [
            "grant_type" => "client_credentials"
        ];
        try {
            $curl = $this->curlFactory->create();
            $curl->addHeader("Content-Type", "application/json");
            $curl->addHeader("Authorization", "Basic " . base64_encode($username . ":" . $password));
            $curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $curl->post($tokenApi, $data);
            $response = $curl->getBody();
            $response = (array)json_decode($response);
            return $response['access_token'];
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Function returning config value
     *
     * @param string $configPath
     * @return mixed
     */
    public function getConfigValue(string $configPath): mixed
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($configPath, $storeScope);
    }
}
