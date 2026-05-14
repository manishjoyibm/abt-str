<?php

namespace Abbott\PriceInvGql\Model\Sales\Total\Profile\Total\Group;

use Magento\Framework\Exception\NoSuchEntityException;

class Regular extends \Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group\Regular
{
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
        $option = $this->getItemOption($item);
        if ($option) {
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
}
