<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CheckoutStep implements ArrayInterface
{
    public const SHIPPING_STEP = 2;
    public const SHIPPING_STEP_BEFORE = 7;
    public const PAYMENT_STEP = 3;
    public const SHIPPING_METHODS = 4;
    public const SHIPPING_METHODS_AFTER = 8;
    public const PAYMENT_PLACE_ORDER = 5;
    public const ORDER_SUMMARY = 6;
    public const NONE = 999;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $stepId => $label) {
            $optionArray[] = ['value' => $stepId, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SHIPPING_STEP_BEFORE => __('Above Shipping Address'),
            self::SHIPPING_STEP => __('Below Shipping Address'),
            self::SHIPPING_METHODS => __('Above Shipping Methods'),
            self::SHIPPING_METHODS_AFTER => __('Below Shipping Methods'),
            self::PAYMENT_STEP => __('Above Payment Method'),
            self::PAYMENT_PLACE_ORDER => __('Below Payment Method'),
            self::ORDER_SUMMARY => __('Order Summary'),
            self::NONE => __('None'),
        ];
    }
}
