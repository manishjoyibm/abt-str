<?php

namespace Abbott\MyAccount\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Action class for My Account:Action to be set
 */
class Action implements ArrayInterface
{
    public const EXCLUDE_SELECTED = 1;

    /**
     * Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::EXCLUDE_SELECTED,
                'label' => __('Hide all selected links')
            ]
        ];
    }
}
