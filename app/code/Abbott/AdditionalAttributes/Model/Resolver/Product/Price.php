<?php

declare(strict_types=1);

namespace Abbott\AdditionalAttributes\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Store\Api\Data\StoreInterface;

class Price implements ResolverInterface
{
    const AMOUNT = 'amount';

    const VALUE  = 'value';
    /**
     * @var PriceInfoFactory
     */
    private $priceInfoFactory;

    /**
     * @param PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        PriceInfoFactory $priceInfoFactory
    ) {
        $this->priceInfoFactory = $priceInfoFactory;
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
        $product = $value['model'];
        $product->unsetData('minimal_price');
        $priceInfo = $this->priceInfoFactory->create($product);
        $regularPriceAmount =  $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getAmount();
        $store = $context->getExtensionAttributes()->getStore();

        $price = $this->createAdjustmentsArray(
            $priceInfo->getAdjustments(),
            $regularPriceAmount,
            $store
        );
        return $price[self::AMOUNT][self::VALUE];
    }

    /**
     * Fill a price with an adjustment array structure with amounts from an amount type
     *
     * @param AdjustmentInterface[] $adjustments
     * @param AmountInterface $amount
     * @param StoreInterface $store
     * @return array
     */
    private function createAdjustmentsArray(array $adjustments, AmountInterface $amount, StoreInterface $store) : array
    {
        $priceArray = [
                self::AMOUNT => [
                    self::VALUE => $amount->getValue(),
                    'currency' => $store->getCurrentCurrencyCode()
                ],
                'adjustments' => []
            ];
        $priceAdjustmentsArray = [];
        foreach ($adjustments as $adjustmentCode => $adjustment) {
            if ($amount->hasAdjustment($adjustmentCode) && $amount->getAdjustmentAmount($adjustmentCode)) {
                $priceAdjustmentsArray[] = [
                    'code' => strtoupper($adjustmentCode),
                    self::AMOUNT => [
                        self::VALUE => $amount->getAdjustmentAmount($adjustmentCode),
                        'currency' => $store->getCurrentCurrencyCode(),
                    ],
                    'description' => $adjustment->isIncludedInDisplayPrice() ?
                        'INCLUDED' : 'EXCLUDED'
                ];
            }
        }
        $priceArray['adjustments'] = $priceAdjustmentsArray;
        return $priceArray;
    }
}
