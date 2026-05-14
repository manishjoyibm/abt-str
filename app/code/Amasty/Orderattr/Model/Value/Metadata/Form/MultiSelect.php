<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Value\Metadata\Form;

use Amasty\Orderattr\Model\Value\Metadata\Form;

class MultiSelect extends \Magento\Eav\Model\Attribute\Data\Multiselect
{
    /**
     * @inheritdoc
     */
    public function compactValue($value)
    {
        if ($value === false) {
            $value = '';
        }

        return parent::compactValue($value);
    }

    /**
     * @param string $format
     * @return array|string
     */
    public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = parent::outputValue($format);
        if (($format === Form::FORMAT_TO_VALIDATE_RELATIONS) && $value) {
            $value = [$value];
        }

        return $value;
    }
}
