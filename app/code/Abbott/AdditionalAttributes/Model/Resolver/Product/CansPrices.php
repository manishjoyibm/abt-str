<?php

declare(strict_types=1);

namespace Abbott\AdditionalAttributes\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class CansPrices implements ResolverInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    public $metadataPool;
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $finalXPrice = 0.0;
        $finalYPrice = 0.0;
        $product = $value['model'];
        $discount = $product->getData('custom_discount');
        $canx = $product->getData('cans_x');
        $price = $product->getData('price');
        $cany = $product->getData('cans_y');

        if (isset($canx)) {
            $finalXPrice = $this->getCanPrice($price, $canx);
        }

        if (isset($cany)) {
            $finalYPrice = $this->getCanPrice($price, $cany);
        }

        $cans['cans_x_price'] = $finalXPrice;
        $cans['cans_x_number'] = $this->getCanCount($canx);
        $cans['cans_x_price_discount'] = $this->getCanDiscPrice($finalXPrice, $discount);
        $cans['cans_y_price'] = $finalYPrice;
        $cans['cans_y_number'] = $this->getCanCount($cany);
        $cans['cans_y_price_discount'] = $this->getCanDiscPrice($finalYPrice, $discount);

        return $cans;
    }

    private function getCanPrice($price, $can)
    {
        return $price * $this->getCanCount($can);
    }

    private function getCanCount($can)
    {
        $canxCount = explode(" ", $can);
        return (int)$canxCount[1];
    }

    private function getCanDiscPrice($canPrice, $discount)
    {
        return round($canPrice-(($canPrice*$discount)/100), 2);
    }
}
