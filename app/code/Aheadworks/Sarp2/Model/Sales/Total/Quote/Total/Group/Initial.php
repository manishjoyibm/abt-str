<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group;

use Aheadworks\Sarp2\Model\Sales\Total\Group\AbstractGroup;

/**
 * Class Initial
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group
 */
class Initial extends AbstractGroup
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
        $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
        if ($optionId) {
            $option = $this->optionRepository->get($optionId->getValue());
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
