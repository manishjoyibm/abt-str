<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Quote\Item\Checker;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription as ProductChecker;
use Magento\Quote\Model\Quote\Item;

/**
 * Class IsSubscription
 * @package Aheadworks\Sarp2\Model\Quote\Item\Checker
 */
class IsSubscription
{
    /**
     * @var ProductChecker
     */
    private $productChecker;

    /**
     * @param ProductChecker $productChecker
     */
    public function __construct(ProductChecker $productChecker)
    {
        $this->productChecker = $productChecker;
    }

    /**
     * todo: consider \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item argument type, M2SARP-383
     * Check if quote item is subscription
     *
     * @param Item $item
     * @return bool
     */
    public function check($item)
    {
        $product = $item->getParentItem() ? $item->getParentItem()->getProduct() : $item->getProduct();
        if ($this->productChecker->check($product)) {
            $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
            return $optionId && $optionId->getValue();
        }
        return false;
    }
}
