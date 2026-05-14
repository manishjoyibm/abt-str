<?php


namespace Abbott\GPAS\Helper;


use Abbott\GPAS\Model\Cookie\QrCode;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class Data
 * @package Abbott\GPAS\Helper
 */
class Data extends AbstractHelper
{

    /**
     * Config if module should be enabled
     */
    const IS_ENABLED = "GPAS/general/enable";
    /**
     * Config for API url
     */
    const API_URL = "GPAS/general/api_url";
    /**
     * Config for API key
     */
    const API_KEY = "GPAS/general/api_key";
    /**
     * Config for enable logging
     */
    const ENABLE_LOGGING = "GPAS/general/enable_logging";

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(Context $context, EncryptorInterface $encryptor)
    {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    /**
     * @return string
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(self::IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getApiUrl() {
        return $this->scopeConfig->getValue(self::API_URL);
    }

    /**
     * @return string
     */
    public function getApiKey() {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::API_KEY));
    }

    /**
     * @return string
     */
    public function getEnableLogging() {
        return $this->scopeConfig->getValue(self::ENABLE_LOGGING);
    }
}
