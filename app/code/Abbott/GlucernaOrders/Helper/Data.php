<?php

namespace Abbott\GlucernaOrders\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper class for Glucerna Orders:Backorder Message
 */
class Data extends AbstractHelper
{
    public $_storeManager;
    public const XML_PATH_GLUCERNAORDER = 'glucerna_order/backorder_message/message';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    
    public function getConfig()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GLUCERNAORDER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
    
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
