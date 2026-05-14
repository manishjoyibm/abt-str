<?php

namespace Abbott\Quote\Plugin\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Compare;

class ComparePlugin
{
    /**
     * Before submit method
     *
     * @param Compare $subject
     * @param bool $result
     * @param Item $target
     * @param Item $compared
     * @return bool
     */
    public function afterCompare(Compare $subject, bool $result, Item $target, Item $compared): bool
    {
        if ($target->getSku() !== null && $target->getSku() === $compared->getSku()) {
            if ($target->getProductId() != $compared->getProductId()) {
                return false;
            }

            $targetOptionByCode = $target->getOptionsByCode();
            $comparedOptionsByCode = $compared->getOptionsByCode();
            if (!$target->compareOptions($targetOptionByCode, $comparedOptionsByCode)) {
                return false;
            }
            if (!$target->compareOptions($comparedOptionsByCode, $targetOptionByCode)) {
                return false;
            }
        }

        return $result;
    }
}
