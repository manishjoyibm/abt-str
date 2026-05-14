<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model;

class DateFormat
{
    public const FORMAT_MAP = [
        'dd'   => 'd',
        'd'    => 'j',
        'MM'   => 'm',
        'M'    => 'n',
        'yyyy' => 'Y',
        'yy'   => 'y',
        'y'   => 'Y',
        'mm' => 'i',
    ];

    public function convert(string $value): string
    {
        foreach (static::FORMAT_MAP as $search => $replace) {
            $value = preg_replace('/(^|[^%])' . $search . '/', '$1' . $replace, $value);
        }

        return $value;
    }
}
