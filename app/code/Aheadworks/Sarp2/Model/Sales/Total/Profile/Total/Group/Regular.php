<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

/**
 * Class Regular
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
class Regular extends AbstractProfileGroup
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::CODE_REGULAR;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemPrice($item, $useBaseCurrency)
    {
        $result = 0.0;
        $option = $this->getItemOption($item);
        if ($option) {
            $plan = $option->getPlan();
            $product = $this->getProduct($item);

            $baseItemPrice = $option->getIsAutoRegularPrice()
                ? $this->priceCalculation->getAutoRegularPrice($product->getEntityId(), $plan->getPlanId())
                : (float)$option->getRegularPrice();
            $result = $useBaseCurrency
                ? $baseItemPrice
                : $this->priceCurrency->convert($baseItemPrice);
        }
        $result = $this->customOptionCalculator->applyOptionsPrice($item, $result, $useBaseCurrency);

        return $result;
    }
}
