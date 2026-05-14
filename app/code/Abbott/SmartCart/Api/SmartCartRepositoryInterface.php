<?php

namespace Abbott\SmartCart\Api;

use Abbott\SmartCart\Api\Data\SmartCartInterface;

/**
 * Interface SmartCartRepositoryInterface
 */
interface SmartCartRepositoryInterface
{
    /**
     * GetSmartCartByCode
     *
     * @param string $code
     * @param int $storeId
     * @param bool $isActive
     * @return SmartCartInterface
     */
    public function getSmartCartByCode($code, $storeId = null, $isActive = true);
}
