<?php

namespace Abbott\PowerbiExport\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ReportStatus
 */
class ReportStatus implements OptionSourceInterface
{

    const YES = 1;
    const NO  = 0;

    public static function getOptionArray()
    {
        return [
            self::YES => __('Yes'),
            self::NO => __('No')
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];

        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }

        return $res;
    }
}
