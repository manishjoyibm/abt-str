<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Abbott\OneTrust\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Option implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('DEV')],
            ['value' => 1, 'label' => __('STAGING')],
            ['value' => 2, 'label' => __('PROD')]
        ];
    }
}
