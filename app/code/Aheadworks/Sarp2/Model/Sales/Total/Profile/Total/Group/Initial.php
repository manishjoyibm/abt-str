<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

/**
 * Class Initial
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
class Initial extends AbstractProfileGroup
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::CODE_INITIAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemPrice($item, $useBaseCurrency)
    {
        $result = 0.0;
        $option = $this->getItemOption($item);
        if ($option) {
            $planDefinition = $option->getPlan()->getDefinition();

            $initialFee = $option->getInitialFee();
            $baseItemPrice = $initialFee != 0 && $planDefinition->getIsInitialFeeEnabled()
                ? (float)$initialFee
                : 0;
            $result = $useBaseCurrency
                ? $baseItemPrice
                : $this->priceCurrency->convert($baseItemPrice);
        }
        return $result;
    }
}
