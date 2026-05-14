<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor as SubscriptionOptionProcessor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class Generic
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Details\Config
 */
class Generic implements ProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionsRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var SubscriptionOptionProcessor
     */
    private $subscriptionOptionProcessor;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     * @param PlanRepositoryInterface $planRepository
     * @param SubscriptionOptionProcessor $subscriptionOptionProcessor
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SubscriptionOptionRepositoryInterface $optionsRepository,
        PlanRepositoryInterface $planRepository,
        SubscriptionOptionProcessor $subscriptionOptionProcessor,
        TaxHelper $taxHelper
    ) {
        $this->productRepository = $productRepository;
        $this->optionsRepository = $optionsRepository;
        $this->planRepository = $planRepository;
        $this->subscriptionOptionProcessor = $subscriptionOptionProcessor;
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
        $config = [];

        $subscriptionOptions = $this->optionsRepository->getList($productId);
        /** @var SubscriptionOptionInterface $option */
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
            $optionId = $item ? $option->getPlanId() : $option->getOptionId();
            $config[$optionId] = $detailedOptions;
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

        $priceOptions = [0 => $this->subscriptionOptionProcessor->getProductPrices($product)];

        $subscriptionOptions = $this->optionsRepository->getList($productId);
        foreach ($subscriptionOptions as $option) {
            $priceOptions[$option->getOptionId()] = $this->subscriptionOptionProcessor->getOptionPrices(
                $option,
                $this->taxHelper->displayPriceExcludingTax()
            );
        }
        return [
            'productType' => $product->getTypeId(),
            'options' => $priceOptions
        ];
    }
}
