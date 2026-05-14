<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class YesRecommendedNo
 * @package Aheadworks\Sarp2\Model\Config\Source
 */
class YesRecommendedNo implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Yes (Recommended)')
            ],
            [
                'value' => 0,
                'label' => __('No')
            ]
        ];
    }
}
