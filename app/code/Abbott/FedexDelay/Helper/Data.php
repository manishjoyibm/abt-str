<?php

namespace Abbott\FedexDelay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;
use Psr\Log\LoggerInterface;
use Magento\Variable\Model\Variable;

/**
 * Helper For FedexDelay
 */
class Data extends AbstractHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;
    /**
     * @var \Magento\Variable\Model\Variable
     */
    public $variable;
    const XML_PATH_FEDEX_DELAY_ENABLE = 'fedex_settings/fedex_delay_configuration/is_delayed';
    const XML_PATH_FEDEX_MESSAGE = 'fedex_settings/fedex_delay_configuration/delay_message';

    protected $storeManager;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var InlineTranslation
     */
    protected $inlineTranslation;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Variable $variable,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->variable = $variable;
        parent::__construct($context);
    }

     /**
      * Get the store Id
      * @return int
      */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
     /**
      * Get the Module Config
      * @param $path
      * @param int $storeId
      * @return mixed
      */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Status of Module.
     * @return mixed
     */
    public function getFedexValue()
    {
        return $this->getModuleConfig(self::XML_PATH_FEDEX_DELAY_ENABLE);
    }

    /**
     * Get Status of Module.
     * @return mixed
     */
    public function getFedaxMessage()
    {
        return $this->getModuleConfig(self::XML_PATH_FEDEX_MESSAGE);
    }
}
