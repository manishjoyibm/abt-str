<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\CustomerData\Cart;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Magento\Quote\Model\Quote\Item;

/**
 * Class ItemDataProcessor
 * @package Aheadworks\Sarp2\CustomerData\Cart
 */
class ItemDataProcessor
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @param IsSubscription $isSubscriptionChecker
     */
    public function __construct(IsSubscription $isSubscriptionChecker)
    {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
    }

    /**
     * Process cart item data
     *
     * @param Item $item
     * @param array $data
     * @return array
     */
    public function process(Item $item, array $data)
    {
        $isSubscription = $this->isSubscriptionChecker->check($item);
        $data['aw_sarp_is_subscription'] = $isSubscription;
        if ($isSubscription) {
            $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
            $parentOptionId = $item->getOptionByCode('aw_sarp2_parent_subscription_type');
            if ($parentOptionId) {
                $data['aw_sarp_subscription_type'] = $parentOptionId->getValue();
            } elseif ($optionId) {
                $data['aw_sarp_subscription_type'] = $optionId->getValue();
            }
        }
        return $data;
    }
}
