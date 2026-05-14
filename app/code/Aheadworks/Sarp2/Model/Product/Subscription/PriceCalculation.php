<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculationInterface;
use Aheadworks\Sarp2\Model\Plan\Source\PriceRounding;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class OptionRepository
 * @package Aheadworks\Sarp2\Model\Product\Subscription
 */
class PriceCalculation implements SubscriptionPriceCalculationInterface
{
    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param PlanRepositoryInterface $planRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        PlanRepositoryInterface $planRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->planRepository = $planRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoTrialPrice($productId, $planId)
    {
        $product = $this->productRepository->getById($productId);
        $plan = $this->planRepository->get($planId);
        return $this->calculateAndRound(
            $product->getPrice(),
            $plan->getTrialPricePatternPercent(),
            $plan->getPriceRounding()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoRegularPrice($productId, $planId)
    {
        $product = $this->productRepository->getById($productId);
        $plan = $this->planRepository->get($planId);
        return $this->calculateAndRound(
            $product->getPrice(),
            $plan->getRegularPricePatternPercent(),
            $plan->getPriceRounding()
        );
    }

    /**
     * Calculate and round subscription price
     *
     * @param $price
     * @param $percent
     * @param $rounding
     * @return float
     */
    private function calculateAndRound($price, $percent, $rounding)
    {
        $result = $price * $percent / 100;

        $intPart = floor($result);
        if ($rounding == PriceRounding::UP_TO_XX_99) {
            $result = $intPart + 0.99;
        } elseif ($rounding == PriceRounding::UP_TO_XX_90) {
            $result = $intPart + 0.9;
        } elseif ($rounding == PriceRounding::DOWN_TO_XX_99) {
            if ($intPart > 1) {
                $intPart -= 1;
            }
            $result = $intPart + 0.99;
        } elseif ($rounding == PriceRounding::DOWN_TO_XX_90) {
            if ($intPart > 1) {
                $intPart -= 1;
            }
            $result = $intPart + 0.9;
        }

        $tens = floor($intPart / 10);
        if ($rounding == PriceRounding::UP_TO_X9_00) {
            if ($tens == 0) {
                $result = 9;
            } else {
                if (($intPart < ($tens * 10 + 9)) && ($price > ($tens * 10 + 9))) {
                    $result = $tens * 10 + 9;
                } elseif (($intPart >= ($tens * 10 + 9)) && ($price > (($tens + 1) * 10 + 9))) {
                    $result = ($tens + 1) * 10 + 9;
                } else {
                    $result = ($tens - 1) * 10 + 9;
                }
            }
        } elseif ($rounding == PriceRounding::DOWN_TO_X9_00) {
            if ($tens == 0) {
                $result = 0.01;
            } else {
                if ($result < ($tens * 10 + 9)) {
                    $result = $tens > 1 ? ($tens - 1) * 10 + 9 : 9;
                } else {
                    $result = $tens * 10 + 9;
                }
            }
        }

        return $result;
    }
}
