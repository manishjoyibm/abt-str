<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\OptionFactory as ConfigurationItemOptionFactory;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

/**
 * Class CustomOptionCalculator
 *
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
class CustomOptionCalculator
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ConfigurationItemOptionFactory
     */
    private $configurationItemOptionFactory;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurationItemOptionFactory $configurationItemOptionFactory
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        ConfigurationItemOptionFactory $configurationItemOptionFactory
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->configurationItemOptionFactory = $configurationItemOptionFactory;
    }

    /**
     * Apply custom options price
     *
     * @param ProfileItemInterface $item
     * @param float $basePrice
     * @param bool $useBaseCurrency
     * @return float
     */
    public function applyOptionsPrice($item, $basePrice, $useBaseCurrency)
    {
        $product = $item->getParentItem() ? $item->getParentItem()->getProduct() : $item->getProduct();
        $productOptions = $item->getProductOptions();

        $baseProductPrice = $this->getProduct($item)->getPrice();
        $finalPrice = $basePrice;
        if ($productOptions && isset($productOptions['options'])) {
            $optionsPrice = 0;
            foreach ($productOptions['options'] as $optionConfig) {
                if ($option = $product->getOptionById($optionConfig['option_id'])) {
                    $confItemOption = $this->getConfigurationItemOption($option, $optionConfig);

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
     * @param ProfileItemInterface $item
     * @return ProductInterface|Product
     */
    private function getProduct($item)
    {
        if ($item->hasChildItems()) {
            $children = $item->getChildItems();
            $child = reset($children);
            $product = $child->getProduct();
        } else {
            $product = $item->getProduct();
        }
        return $product;
    }

    /**
     * Get configuration product option
     *
     * @param ProductCustomOptionInterface $option
     * @param array $optionConfig
     * @return OptionInterface
     */
    private function getConfigurationItemOption($option, $optionConfig)
    {
        $confItemOption = $this->configurationItemOptionFactory->create();
        $confItemOption
            ->setData('option_id', $optionConfig['option_id'])
            ->setData('value', $optionConfig['option_value'])
            ->setData('product_id', $option->getProductId());

        return $confItemOption;
    }
}
