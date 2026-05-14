<?php

namespace Abbott\CheckoutCaptcha\Block\LayoutProcessor\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class Onepage implements LayoutProcessorInterface
{
    public const CHILDREN = 'children';

    public function process($jsLayout)
    {
        $jsLayout['components']['checkout'][self::CHILDREN]['steps'][self::CHILDREN]['billing-step'][self::CHILDREN]
                     ['payment'][self::CHILDREN]['payments-list'][self::CHILDREN]['msp_recaptcha']['settings'] = 0;
        return $jsLayout;
    }
}
