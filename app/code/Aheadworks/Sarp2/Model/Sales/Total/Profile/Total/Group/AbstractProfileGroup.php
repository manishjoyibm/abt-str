<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Aheadworks\Sarp2\Model\Sales\Total\Group\AbstractGroup;
use Aheadworks\Sarp2\Model\Profile\Item;
use Magento\Catalog\Api\Data\ProductInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculationInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorFactory;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class AbstractProfileGroup
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
abstract class AbstractProfileGroup extends AbstractGroup
{
    /**
     * @var SubscriptionOptionInterfaceFactory
     */
    protected $subscriptionOptionFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomOptionCalculator
     */
    protected $customOptionCalculator;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param SubscriptionPriceCalculationInterface $priceCalculation
     * @param PriceCurrencyInterface $priceCurrency
     * @param PopulatorFactory $populatorFactory
     * @param ProviderInterface $provider
     * @param SubscriptionOptionInterfaceFactory $subscriptionOptionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomOptionCalculator $customOptionCalculator
     * @param array $populateMaps
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        SubscriptionPriceCalculationInterface $priceCalculation,
        PriceCurrencyInterface $priceCurrency,
        PopulatorFactory $populatorFactory,
        ProviderInterface $provider,
        SubscriptionOptionInterfaceFactory $subscriptionOptionFactory,
        DataObjectHelper $dataObjectHelper,
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
        $this->subscriptionOptionFactory = $subscriptionOptionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customOptionCalculator = $customOptionCalculator;
    }

    /**
     * Retrieve item option
     *
     * @param Item $item
     * @return \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getItemOption($item)
    {
        $option = null;
        $productOptions = $item->getProductOptions();
        if (isset($productOptions['info_buyRequest']['aw_sarp2_subscription_option'])) {
            $option = $this->subscriptionOptionFactory->create();
            $optionArray = $productOptions['info_buyRequest']['aw_sarp2_subscription_option'];
            $this->dataObjectHelper->populateWithArray($option, $optionArray, SubscriptionOptionInterface::class);
        } else {
            $optionId = $productOptions['info_buyRequest']['aw_sarp2_subscription_type'];
            if ($optionId) {
                $option = $this->optionRepository->get($optionId);
            }
        }
        return $option;
    }

    /**
     * Get product
     *
     * @param Item $item
     * @return ProductInterface
     */
    protected function getProduct($item)
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
}
