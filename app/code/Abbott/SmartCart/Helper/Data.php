<?php

namespace Abbott\SmartCart\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_ENABLED = 'smart_cart/general/is_enabled';
    public const XML_PATH_SHOW_CART = 'smart_cart/guest_cart/show_cart';

    /**
     * Is Smart Cart enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Show Cart to Guest User
     *
     * @return bool
     */
    public function canShowCart()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_CART,
            ScopeInterface::SCOPE_STORE
        );
    }
}
