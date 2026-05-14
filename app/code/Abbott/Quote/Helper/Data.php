<?php


namespace Abbott\Quote\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Configuration for enable/disable
     */
    private const IS_ENABLE_QTY_LIMIT = "quote_abbott/general/enable_qty_limit";

    /**
     * Check if Qty Limit feature is Enabled
     *
     * @return bool
     */
    public function isQtyLimitEnabled(): bool
    {
        return $this->scopeConfig->getValue(
            self::IS_ENABLE_QTY_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
