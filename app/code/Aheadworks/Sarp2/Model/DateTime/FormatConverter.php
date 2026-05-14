<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\DateTime;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class FormatConverter
 * @package Aheadworks\Sarp2\Model\DateTime
 */
class FormatConverter
{
    const DATA_FORMAT = '/d+/i';
    const YEAR_FORMAT = '/y+/i';
    const MONTH_FORMAT = '/m+/i';
    const FORMAT = '/\s+\S+/';
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * Converts PHP IntlFormatter format to \DateTime format
     *
     * @param string $format
     * @return string
     */
    public function convertToDateTimeFormat($format = null)
    {
        $format = $format ? : $this->getDateFormat();
        $format = preg_replace(self::DATA_FORMAT, 'd', $format);
        $format = preg_replace(self::MONTH_FORMAT, 'm', $format);
        $format = preg_replace(self::YEAR_FORMAT, 'Y', $format);
        $format = preg_replace(self::FORMAT, '', $format);

        return $format;
    }

    /**
     * Converts PHP IntlFormatter format to Js Calendar format
     *
     * @param string $format
     * @return string
     */
    public function convertToJsCalendarFormat($format = null)
    {
        $format = $format ? : $this->getDateFormat();
        $format = preg_replace(self::DATA_FORMAT, 'dd', $format);
        $format = preg_replace(self::MONTH_FORMAT, 'mm', $format);
        $format = preg_replace(self::YEAR_FORMAT, 'yyyy', $format);
        $format = preg_replace(self::FORMAT, '', $format);

        return $format;
    }

    /**
     * Converts PHP IntlFormatter format to momemt Js format
     *
     * @param string $format
     * @return string
     */
    public function convertToMomentJsFormat($format = null)
    {
        $format = $format ? : $this->getDateFormat();
        $format = preg_replace(self::DATA_FORMAT, 'DD', $format);
        $format = preg_replace(self::MONTH_FORMAT, 'MM', $format);
        $format = preg_replace(self::YEAR_FORMAT, 'YYYY', $format);
        $format = preg_replace(self::FORMAT, '', $format);

        return $format;
    }

    /**
     * Retrieve short date format
     *
     * @return string
     */
    private function getDateFormat()
    {
        return $this->localeDate->getDateFormat();
    }
}
