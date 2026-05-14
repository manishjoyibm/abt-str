<?php

namespace Abbott\ProgressiveDiscount\Model\Rule\Condition;

class Product extends \Magento\SalesRule\Model\Rule\Condition\Product
{
    /**
     * Add special attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_is_progressive'] = __('Is Progressive');
        $attributes['quote_item_is_one_time'] = __('Is One Time');
    }
}
