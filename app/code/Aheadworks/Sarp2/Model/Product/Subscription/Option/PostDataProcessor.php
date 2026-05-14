<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Option;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PostDataProcessor
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Option
 */
class PostDataProcessor
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare option entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data)
    {
        if ($this->storeManager->isSingleStoreMode()) {
            $stores = $this->storeManager->getStores();
            /** @var StoreInterface $store */
            $store = current($stores);
            $data['website_id'] = $store->getWebsiteId();
        }

        $data['is_auto_trial_price'] = !isset($data['trial_price'])
            || $data['trial_price'] == '';
        if ($data['is_auto_trial_price']) {
            $data['trial_price'] = null;
        }
        $data['is_auto_regular_price'] = !isset($data['regular_price'])
            || $data['regular_price'] == '';
        if ($data['is_auto_regular_price']) {
            $data['regular_price'] = null;
        }
        return $data;
    }
}
