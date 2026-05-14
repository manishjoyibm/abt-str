<?php


namespace Abbott\PriceInvGql\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config for error message on checkout
     */
    public const PRODUCT_PAGE_GROUP_MESSAGE_GROUPS = "aboott_message/product_page/group_message_groups";

    /**
     * Get customer group for group Message.
     *
     * @param int $store
     * @return array|string[]
     */
    public function getGroupMessageGroups($store = null)
    {
        $response = [];
        if ($this->scopeConfig->getValue(
            self::PRODUCT_PAGE_GROUP_MESSAGE_GROUPS,
            ScopeInterface::SCOPE_STORE,
            $store
        )) {
            $response = explode(
                ",",
                $this->scopeConfig->getValue(
                    self::PRODUCT_PAGE_GROUP_MESSAGE_GROUPS,
                    ScopeInterface::SCOPE_STORE,
                    $store
                )
            );
        }
        return $response;
    }
}
