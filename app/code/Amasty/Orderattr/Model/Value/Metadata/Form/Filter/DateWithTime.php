<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

/**
 * Form Input/Output Date with time Filter
 */
namespace Amasty\Orderattr\Model\Value\Metadata\Form\Filter;

use Amasty\Orderattr\Model\DateFormat;
use Laminas\Validator\Date as DateValidator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime;

class DateWithTime implements \Magento\Framework\Data\Form\Filter\FilterInterface
{
    /**
     * Sometimes Magento is not returning seconds - remove seconds from pattern before validate
     */
    public const DATETIME_INTERNAL_VALIDATION_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * Local
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var DateFormat
     */
    private $dateFormatConvertor;

    public function __construct(
        $format = null,
        \Magento\Framework\Locale\ResolverInterface $localeResolver = null,
        DateFormat $dateFormatConvertor = null //todo: move to not optional
    ) {
        if ($format === null) {
            $format = DateTime::DATETIME_INTERNAL_FORMAT;
        }
        $this->dateFormat = $format;
        $this->localeResolver = $localeResolver;
        $this->dateFormatConvertor = $dateFormatConvertor ?? ObjectManager::getInstance()->get(DateFormat::class);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value date in format $this->_dateFormat
     *
     * @return string          date in format DateTime::DATETIME_INTERNAL_FORMAT
     */
    public function inputFilter($value)
    {
        if (!$this->validateInputDate($value)) {
            return $value;
        }

        return \DateTime::createFromFormat($this->dateFormatConvertor->convert($this->dateFormat), $value)
            ->format('Y-m-d H:i:s');
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value date in format DateTime::DATETIME_INTERNAL_FORMAT
     *
     * @return string         date in format $this->_dateFormat
     */
    public function outputFilter($value)
    {
        if (!$this->validateOutputDate($value)) {
            return $value;
        }

        $date = (new \DateTime($value))->format($this->dateFormatConvertor->convert($this->dateFormat));

        return str_replace(['am', 'pm'], ['AM', 'PM'], $date);
    }

    /**
     * Sometimes Magento is not returning seconds - remove seconds from pattern before validate
     * if in date pattern will be seconds, it will not passed
     *
     * @param string $value
     *
     * @return bool
     */
    public function validateInputDate($value)
    {
        $dateFormat = $this->dateFormatConvertor->convert($this->dateFormat);

        return $this->validateDate($dateFormat, $value);
    }

    private function validateDate($dateFormat, $value)
    {
        $options = [
            'format' => $dateFormat,
            'locale' => $this->localeResolver->getLocale()
        ];

        return (new DateValidator($options))->isValid($value);
    }

    /**
     * Sometimes Magento is not returning seconds - remove seconds from pattern before validate
     * if in date pattern will be seconds, it will not passed
     *
     * @param string $value
     *
     * @return bool
     */
    public function validateOutputDate($value)
    {
        $dateFormat = self::DATETIME_INTERNAL_VALIDATION_FORMAT;

        return $this->validateDate($dateFormat, $value);
    }
}
