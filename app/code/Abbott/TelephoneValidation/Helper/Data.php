<?php

namespace Abbott\TelephoneValidation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper For ModuleStatus
 */
class Data extends AbstractHelper
{
    const XML_PATH_MODULE_ENABLE = 'telephone_validation/settings/enable';
  
    protected $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
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
    public function getModuleEnable()
    {
        return $this->getModuleConfig(self::XML_PATH_MODULE_ENABLE);
    }
}
