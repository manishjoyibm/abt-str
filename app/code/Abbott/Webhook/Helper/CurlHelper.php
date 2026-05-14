<?php

namespace Abbott\Webhook\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\Store;

class CurlHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $encryptor;
    protected $curl;
    public const XML_ENABLED = 'abbott_webhook/webhook/enable';

    public const XML_USER_NAME = 'abbott_webhook/webhook/username';

    public const XML_ENCRYPTED = 'abbott_webhook/webhook/password';

    public const XML_DEBUG = 'abbott_webhook/webhook/enable_debug';

    public const FLAVORSIZE_URL = 'abbott_webhook/webhook/flavorsize_url';

    public const ATTRIBUTE_CODES = 'abbott_webhook/webhook/attribute_codes';

    /**
     * Constructor
     *
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * PostData
     *
     * @param string $url
     * @return string
     */
    public function postData($url)
    {
        if (!empty($this->getUserName()) && !empty($this->getPassword())) {
            $this->curl->setCredentials($this->getUserName(), $this->getPassword());
        }
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->post($url, null);
        return $this->curl->getBody();
    }

    /**
     * Enable
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function enabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * GetUserName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->scopeConfig->getValue(self::XML_USER_NAME);
    }

    /**
     * GetPassword
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XML_ENCRYPTED));
    }

    /**
     * EnableDebug
     *
     * @return int
     */
    public function enableDebug()
    {
        return $this->scopeConfig->getValue(self::XML_DEBUG);
    }

    /**
     * GetFlavorSizeUrl
     *
     * @return string
     */
    public function getFlavorSizeUrl()
    {
        return $this->scopeConfig->getValue(self::FLAVORSIZE_URL);
    }

    /**
     * GetAttributeCodes
     *
     * @return string
     */
    public function getAttributeCodes()
    {
        return $this->scopeConfig->getValue(self::ATTRIBUTE_CODES);
    }
}
