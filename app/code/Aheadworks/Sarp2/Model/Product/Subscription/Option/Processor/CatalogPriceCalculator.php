<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;

/**
 * Class PriceCalculator
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor
 */
class CatalogPriceCalculator
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Product[]
     */
    private $products;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CatalogHelper $catalogHelper
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CatalogHelper $catalogHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->productRepository = $productRepository;
        $this->catalogHelper = $catalogHelper;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get formatted price
     *
     * @param int $productId
     * @param float $price
     * @param bool $exclTax
     * @return string
     */
    public function getFormattedPrice($productId, $price, $exclTax = true)
    {
        return $this->priceCurrency->format(
            $this->getFinalPriceAmount($productId, $price, $exclTax),
            false
        );
    }

    /**
     * Get old price
     *
     * @param float $price
     * @return float
     */
    public function getOldPriceAmount($price)
    {
        return $this->priceCurrency->convert($price);
    }

    /**
     * Get base price
     *
     * @param int $productId
     * @param float $price
     * @return float
     */
    public function getBasePriceAmount($productId, $price)
    {
        $product = $this->getProduct($productId);
        if ($product) {
            $basePriceAmount = $this->catalogHelper->getTaxPrice(
                $product,
                $price,
                false,
                null,
                null,
                null,
                null,
                null,
                false
            );
            return $this->priceCurrency->convert($basePriceAmount);
        }
        return $price;
    }

    /**
     * Get final price
     *
     * @param int $productId
     * @param float $price
     * @param bool $exclTax
     * @return float
     */
    public function getFinalPriceAmount($productId, $price, $exclTax = true)
    {
        $product = $this->getProduct($productId);
        $includingTax = !$exclTax;
        if ($product) {
            $finalPrice = $this->catalogHelper->getTaxPrice(
                $product,
                $price,
                $includingTax,
                null,
                null,
                null,
                null,
                null,
                false
            );
            return $this->priceCurrency->convert($finalPrice);
        }
        return $price;
    }

    /**
     * Get product
     *
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|Product
     */
    private function getProduct($productId)
    {
        if (!isset($this->products[$productId])) {
            try {
                $this->products[$productId] = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                unset($this->products[$productId]);
            }
        }

        return $this->products[$productId];
    }
}
