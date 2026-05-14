<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TitleResolver
 * @package Aheadworks\Sarp2\Model\Plan
 */
class TitleResolver
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Get plan title for the store specified
     *
     * @param PlanInterface $plan
     * @param null $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTitle($plan, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $planTitles = $plan->getTitles();

        $storeTitle = $this->getStoreTitle($planTitles, $storeId);
        $planTitle = $storeTitle ? $storeTitle : $plan->getName();

        return $planTitle;
    }

    /**
     * Get store title
     *
     * @param PlanTitleInterface[] $planTitles
     * @param $storeId
     * @return string|null
     */
    private function getStoreTitle($planTitles, $storeId)
    {
        $storeTitle = null;
        foreach ($planTitles as $title) {
            if ($title->getStoreId() == $storeId) {
                $storeTitle = $title->getTitle();
                break;
            }
        }

        if (!$storeTitle) {
            $storeTitle = $this->getDefaultTitle($planTitles);
        }

        return $storeTitle;
    }

    /**
     * Get default title
     *
     * @param PlanTitleInterface[] $planTitles
     * @return string|null
     */
    private function getDefaultTitle($planTitles)
    {
        $defaultTitle = null;
        foreach ($planTitles as $title) {
            if ($title->getStoreId() == Store::DEFAULT_STORE_ID) {
                $defaultTitle = $title->getTitle();
                break;
            }
        }

        return $defaultTitle;
    }
}
