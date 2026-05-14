<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group;

use Aheadworks\Sarp2\Model\Sales\Total\Group\AbstractGroup;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculationInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorFactory;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class Trial
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group
 */
class Trial extends AbstractGroup
{
    /**
     * @var CustomOptionCalculator
     */
    private $customOptionCalculator;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param SubscriptionPriceCalculationInterface $priceCalculation
     * @param PriceCurrencyInterface $priceCurrency
     * @param PopulatorFactory $populatorFactory
     * @param ProviderInterface $provider
     * @param CustomOptionCalculator $customOptionCalculator
     * @param array $populateMaps
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        SubscriptionPriceCalculationInterface $priceCalculation,
        PriceCurrencyInterface $priceCurrency,
        PopulatorFactory $populatorFactory,
        ProviderInterface $provider,
        CustomOptionCalculator $customOptionCalculator,
        array $populateMaps = []
    ) {
        parent::__construct(
            $optionRepository,
            $priceCalculation,
            $priceCurrency,
            $populatorFactory,
            $provider,
            $populateMaps
        );
        $this->customOptionCalculator = $customOptionCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::CODE_TRIAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemPrice($item, $useBaseCurrency)
    {
        $result = 0.0;
        $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
        if ($optionId) {
            $option = $this->optionRepository->get($optionId->getValue());
            $plan = $option->getPlan();

            if ($plan->getDefinition()->getIsTrialPeriodEnabled()) {
                $product = $this->getProduct($item);
                $baseItemPrice = $option->getIsAutoTrialPrice()
                    ? $this->priceCalculation->getAutoTrialPrice($product->getEntityId(), $plan->getPlanId())
                    : (float)$option->getTrialPrice();
                $result = $useBaseCurrency
                    ? $baseItemPrice
                    : $this->priceCurrency->convert($baseItemPrice);
            }
        }
        $result = $this->customOptionCalculator->applyOptionsPrice($item, $result, $useBaseCurrency);

        return $result;
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
