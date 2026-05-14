<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Value\Metadata\Form;

use Amasty\Orderattr\Model\DateFormat;
use Laminas\Validator\Date as DateValidator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * EAV Entity Attribute Date with time Data Model
 */
class Date extends \Magento\Eav\Model\Attribute\Data\Date
{
    /**
     * @var \Amasty\Orderattr\Model\ConfigProvider
     */
    protected $configProvider;

    /**
     * @var DateFormat
     */
    private $dateFormatConvertor;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Amasty\Orderattr\Model\ConfigProvider $configProvider,
        DateFormat $dateFormat = null //todo: move to not optional
    ) {
        parent::__construct($localeDate, $logger, $localeResolver);
        $this->configProvider = $configProvider;
        $this->dateFormatConvertor = $dateFormat ?? ObjectManager::getInstance()->get(DateFormat::class);
    }

    /**
     * @param RequestInterface $request
     * @return array|string
     * @throws ValidatorException
     */
    public function extractValue(RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        if ($value && $errors = $this->validateInputDate($value)) {
            throw new ValidatorException(null, null, [$errors]);
        }

        return parent::extractValue($request);
    }

    /**
     * Return Data Form Input/Output Filter
     *
     * @return \Magento\Framework\Data\Form\Filter\FilterInterface|false
     */
    protected function _getFormFilter()
    {
        return new \Magento\Framework\Data\Form\Filter\Date($this->_dateFilterFormat(), $this->_localeResolver);
    }

    /**
     * Get/Set/Reset date filter format
     *
     * @param string|null|false $format
     * @return $this|string
     */
    protected function _dateFilterFormat($format = null)
    {
        if ($format === null) {
            // get format
            return $this->configProvider->getDateFormatJs();
        } elseif ($format === false) {
            // reset value
            $this->_dateFilterFormat = null;
            return $this;
        }

        $this->_dateFilterFormat = $format;
        return $this;
    }

    /**
     * Export attribute value to entity model
     *
     * @param array|string $value
     * @return $this
     */
    public function compactValue($value)
    {
        if ($value !== false) {
            if (empty($value)) {
                $value = null;
            }
            /*
             * avoid snake_case to CamelCale and vice versa convertation
             * because underscore in attribute_code can be lost
             */
            $this->getEntity()->setData($this->getAttribute()->getAttributeCode(), $value);
        }
        return $this;
    }

    private function validateInputDate(string $value): array
    {
        $label = $this->getAttribute()->getStoreLabel();

        if (is_numeric($value)) {
            return [__('"%1" does not fit the entered date format.', $label)];
        }

        $validator = new DateValidator([
            'format' =>   $this->getConvertedFormat(),
            'locale' => $this->_localeResolver->getLocale(),
        ]);
        $validator->setMessage(__('"%1" invalid type entered.', $label), DateValidator::INVALID);
        $validator->setMessage(__('"%1" is not a valid date.', $label), DateValidator::INVALID_DATE);
        $validator->setMessage(
            __('"%1" does not fit the entered date format.', $label),
            DateValidator::FALSEFORMAT
        );
        if (!$validator->isValid($value)) {
            return array_unique($validator->getMessages());
        }

        return [];
    }

    /**
     * Convert date format to format accessible for DateTime class
     */
    private function getConvertedFormat(): string
    {
        $format = $this->_dateFilterFormat();

        return $this->dateFormatConvertor->convert($format);
    }
}
