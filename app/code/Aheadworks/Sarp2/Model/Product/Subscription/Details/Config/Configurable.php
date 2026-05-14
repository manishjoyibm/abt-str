<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Configurable\ChildProcessor;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor as SubscriptionOptionProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class Configurable
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Details\Config
 */
class Configurable implements ProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var SubscriptionOptionProcessor
     */
    private $subscriptionOptionProcessor;

    /**
     * @var ChildProcessor
     */
    private $childProcessor;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param PlanRepositoryInterface $planRepository
     * @param SubscriptionOptionProcessor $subscriptionOptionProcessor
     * @param ChildProcessor $childProcessor
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PlanRepositoryInterface $planRepository,
        SubscriptionOptionProcessor $subscriptionOptionProcessor,
        ChildProcessor $childProcessor,
        TaxHelper $taxHelper
    ) {
        $this->productRepository = $productRepository;
        $this->planRepository = $planRepository;
        $this->subscriptionOptionProcessor = $subscriptionOptionProcessor;
        $this->childProcessor = $childProcessor;
        $this->taxHelper = $taxHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($productId, $productTypeId, $item = null)
    {
        try {
            $config = [
                'regularPrices' => $this->getRegularPricesConfig($productId),
                'subscriptionDetails' => $this->getSubscriptionDetailsConfig($productId, $item),
                'productType' => $productTypeId,
                'productId' => $productId
            ];
        } catch (\Exception $e) {
            $config = [];
        }
        
        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionDetailsConfig($productId, $item = null, $profile = null)
    {
        $product = $this->productRepository->getById($productId);
        $childProducts = $item
            ? $this->childProcessor->getProductByAttributes($product, $item)
            : $this->childProcessor->getAllowedList($product);
        $config = [];
        /** @var ProductInterface|Product $childProduct */
        foreach ($childProducts as $childProduct) {
            $subscriptionOptions = $this->childProcessor->getSubscriptionOptions($childProduct, $productId);
            foreach ($subscriptionOptions as $option) {
                if ($profile && $profile->getPlanId() == $option->getPlanId()) {
                    $planDefinition = $profile->getProfileDefinition();
                    /** @var ProfileItemInterface $item */
                    if ($item) {
                        $option->setTrialPrice(
                            $this->taxHelper->displayPriceIncludingTax() || $this->taxHelper->displayBothPrices()
                                ? $item->getTrialPriceInclTax()
                                : $item->getTrialPrice()
                        );
                        $option->setRegularPrice(
                            $this->taxHelper->displayPriceIncludingTax() || $this->taxHelper->displayBothPrices()
                                ? $item->getRegularPriceInclTax()
                                : $item->getRegularPrice()
                        );
                    }
                } else {
                    $planDefinition = $this->planRepository->get($option->getPlanId())->getDefinition();
                }

                $detailedOptions = $this->subscriptionOptionProcessor->getDetailedOptions(
                    $option,
                    $planDefinition,
                    $this->taxHelper->displayPriceExcludingTax(),
                    $profile !== null
                );
                if ($item) {
                    $config[$option->getPlanId()] = $detailedOptions;
                } else {
                    $config[$option->getOptionId()][$childProduct->getId()] = $detailedOptions;
                }
            }
        }
        return $config;
    }

    /**
     * Get regular prices config
     *
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRegularPricesConfig($productId)
    {
        $product = $this->productRepository->getById($productId);
        $childProducts = $this->childProcessor->getAllowedList($product);
        $priceOptions = [];
        /** @var ProductInterface|Product $childProduct */
        foreach ($childProducts as $childProduct) {
            $productPrices = $this->subscriptionOptionProcessor->getProductPrices($childProduct);
            $priceOptions[0][$childProduct->getId()] = $productPrices;
            $subscriptionOptions = $this->childProcessor->getSubscriptionOptions($childProduct, $productId);
            foreach ($subscriptionOptions as $option) {
                $optionPrices = $this->subscriptionOptionProcessor->getOptionPrices(
                    $option,
                    $this->taxHelper->displayPriceExcludingTax()
                );
                $priceOptions[$option->getOptionId()][$childProduct->getId()] = $optionPrices;
            }
        }
        return [
            'productType' => $product->getTypeId(),
            'options' => $priceOptions
        ];
    }
}
