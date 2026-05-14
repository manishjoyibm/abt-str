<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class CustomOptionCalculator
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group
 */
class CustomOptionCalculator
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Apply custom options price
     *
     * @param ItemInterface|AbstractItem $item
     * @param float $basePrice
     * @param bool $useBaseCurrency
     * @return float
     */
    public function applyOptionsPrice($item, $basePrice, $useBaseCurrency)
    {
        $product = $item->getParentItem() ? $item->getParentItem()->getProduct() : $item->getProduct();
        $optionIds = $product->getCustomOption('option_ids');
        $baseProductPrice = $this->getProduct($item)->getPrice();
        $finalPrice = $basePrice;
        if ($optionIds) {
            $optionsPrice = 0;
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $optionsPrice += $group->getOptionPrice($confItemOption->getValue(), $baseProductPrice);
                }
            }
            $optionsPrice = $useBaseCurrency
                ? $optionsPrice
                : $this->priceCurrency->convert($optionsPrice);

            $finalPrice += $optionsPrice;
        }

        return $finalPrice;
    }

    /**
     * Get product
     *
     * @param ItemInterface|AbstractItem $item
     * @return ProductInterface|Product
     */
    private function getProduct($item)
    {
        if ($item instanceof AbstractItem
            && $item->getHasChildren()
        ) {
            $children = $item->getChildren();
            $child = reset($children);
            $product = $child->getProduct();
        } else {
            $product = $item->getProduct();
        }
        return $product;
    }
}
