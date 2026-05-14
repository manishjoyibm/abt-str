<?php

namespace Abbott\Catalog\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DISABLE_SALE = 'abbott_catalog/general/disable_sale';

    const DISABLE_SALE_SORT_ORDER = 'abbott_catalog/general/disable_sale_sort_order';

    /**
     * @return integer
     */
    public function isDisableSaleEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::DISABLE_SALE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return integer
     */
    public function isDisableSaleSortOrderEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::DISABLE_SALE_SORT_ORDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
