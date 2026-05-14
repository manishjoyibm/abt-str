<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Plan\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class BillingPeriod
 * @package Aheadworks\Sarp2\Model\Plan\Source
 */
class BillingPeriod implements ArrayInterface
{
    /**
     * 'Day' billing period
     */
    const DAY = 'day';

    /**
     * 'Week' billing period
     */
    const WEEK = 'week';

    /**
     * 'SemiMonth' billing period
     */
    const SEMI_MONTH = 'semi_month';

    /**
     * 'Month' billing period
     */
    const MONTH = 'month';

    /**
     * 'Year' billing period
     */
    const YEAR = 'year';

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::DAY,
                    'label' => __('Day')
                ],
                [
                    'value' => self::WEEK,
                    'label' => __('Week')
                ],
                [
                    'value' => self::SEMI_MONTH,
                    'label' => __('SemiMonth')
                ],
                [
                    'value' => self::MONTH,
                    'label' => __('Month')
                ],
                [
                    'value' => self::YEAR,
                    'label' => __('Year')
                ]
            ];
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $optionItem) {
            $options[$optionItem['value']] = $optionItem['label'];
        }
        return $options;
    }
}
