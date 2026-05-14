<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

/**
 * Class Trial
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
class Trial extends AbstractProfileGroup
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::CODE_TRIAL;
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

            if ($plan->getDefinition()->getIsTrialPeriodEnabled()) {
                $product = $this->getProduct($item);
                $baseItemPrice = $option->getIsAutoTrialPrice()
                    ? $this->priceCalculation->getAutoTrialPrice($product->getEntityId(), $plan->getPlanId())
                    : (float)$option->getTrialPrice();
                $result = $useBaseCurrency
                    ? $baseItemPrice
                    : $this->priceCurrency->convert($baseItemPrice);
            }
        }
        $result = $this->customOptionCalculator->applyOptionsPrice($item, $result, $useBaseCurrency);

        return $result;
    }
}
