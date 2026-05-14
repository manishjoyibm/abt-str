<?php

namespace Abbott\PriceInvGql\Model\Sales\Total\Quote\Total\Group;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculationInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorFactory;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Regular extends \Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Regular
{
    /**
     * @var Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\CustomOptionCalculator
     */
    private $customOptionCalculator;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param SubscriptionPriceCalculationInterface $priceCalculation
     * @param PriceCurrencyInterface $priceCurrency
     * @param PopulatorFactory $populatorFactory
     * @param ProviderInterface $provider
     * @param Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\CustomOptionCalculator $customOptionCalculator
     * @param array $populateMaps
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        SubscriptionPriceCalculationInterface $priceCalculation,
        PriceCurrencyInterface $priceCurrency,
        PopulatorFactory $populatorFactory,
        ProviderInterface $provider,
        \Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\CustomOptionCalculator $customOptionCalculator,
        array $populateMaps = []
    ) {
        parent::__construct(
            $optionRepository,
            $priceCalculation,
            $priceCurrency,
            $populatorFactory,
            $provider,
            $customOptionCalculator,
            $populateMaps
        );
        $this->customOptionCalculator = $customOptionCalculator;
    }

    /**
     * GetItemPrice function
     *
     * @param $item
     * @param $useBaseCurrency
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getItemPrice($item, $useBaseCurrency)
    {
        $result = 0.0;
        $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
        if ($optionId) {
            $option = $this->optionRepository->get($optionId->getValue());
            $plan = $option->getPlan();
            $product = $this->getProduct($item);

            $baseItemPrice = $option->getIsAutoRegularPrice()
            ? $this->priceCalculation->getAutoRegularPriceCustomerGroup($product->getEntityId(), $plan->getPlanId())
            : $this->priceCalculation->getAutoRegularPriceForCustomer(
                $product->getEntityId(),
                (float)$option->getRegularPrice()
            );
            $result = $useBaseCurrency
            ? $baseItemPrice
            : $this->priceCurrency->convert($baseItemPrice);
        }

        return $this->customOptionCalculator->applyOptionsPrice($item, $result, $useBaseCurrency);
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
