<?php

namespace Abbott\Checkout\Model\Attribute\Data\Plugin;

use Magento\Framework\Locale\Resolver;

class Postcode
{
    const INPUT_VALIDATION = 'input_validation';

    /**
     * Is PCRE is compiled with UTF-8 and Unicode support
     *
     * @var mixed
     **/
    protected $unicodeEnabled;

    /**
     * The Alphabet means english alphabet.
     *
     * @var boolean
     */
    protected $meansEnglishAlphabet;

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @param Resolver $localeResolver
     */
    public function __construct(
        Resolver $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    /**
     * @param \Magento\Customer\Model\Attribute\Data\Postcode $subject
     * @param array $result
     * @param array|string $value
     * @return array
     */
    public function afterValidateValue(
        \Magento\Customer\Model\Attribute\Data\Postcode $subject,
        $result,
        $value
    ) {
        $attribute = $subject->getAttribute();
        $validationRules = $attribute->getValidateRules();

        if (!empty($validationRules[self::INPUT_VALIDATION]) &&
            ($validationRules[self::INPUT_VALIDATION] === "abt-zipcode")
        ) {
            $validateNumHypResult = $this->validateZipCode($attribute, $value);
            $numHypLength = count($validateNumHypResult);
            $result = $this->getResult($result, $numHypLength, $validateNumHypResult);
        }

        if (!empty($validationRules[self::INPUT_VALIDATION]) &&
            ($validationRules[self::INPUT_VALIDATION] === "abt-numeric-with-hyphen-spaces")
        ) {
            $validateNumHypResult = $this->validateNumHypSpaces($attribute, $value);
            $numHypLength = count($validateNumHypResult);
            $result = $this->getResult($result, $numHypLength, $validateNumHypResult);
        }

        return $result;
    }

    /**
     * @param array $result
     * @param int $length
     * @param array $validateResult
     * @return array
     */
    public function getResult($result, $length, $validateResult)
    {
        if (is_array($result) && $length) {
            $result = array_merge($result, $validateResult);
        } elseif ($length) {
            $result = $validateResult;
        }

        return $result;
    }

    /**
     * Validates value ZipCode by attribute rules
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param string $value
     * @return array errors
     */
    public function validateZipCode(\Magento\Eav\Model\Attribute $attribute, string $value): array
    {
        $errors = [];
        $this->checkUnicodeEnglish();

        if ($this->unicodeEnabled) {
            // POSIX named classes are not supported, use alternative a-zA-Z match
            $pattern = '/^[0-9]{5}(-[0-9]{4})?$/';
        } elseif ($this->meansEnglishAlphabet) {
            //The Alphabet means english alphabet.
            $pattern = '/^[0-9]{5}(-[0-9]{4})?$/u';
        } else {
            //The Alphabet means each language's alphabet.
            $pattern = '/^[0-9]{5}(-[0-9]{4})?$/u';
        }
        if ($value == preg_replace($pattern, '', (string) $value)) {
            $label = __($attribute->getStoreLabel());
            $errors[] = __('"%1" must contain valid code.', $label);
        }

        return $errors;
    }

    /**
     * Validates value Numeric with Hyphen Spaces by attribute rules
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param string $value
     * @return array errors
     */
    public function validateNumHypSpaces(\Magento\Eav\Model\Attribute $attribute, string $value): array
    {
        $errors = [];
        $this->checkUnicodeEnglish();

        if ($this->unicodeEnabled) {
            // POSIX named classes are not supported, use alternative a-zA-Z match
            $pattern = '/[^0-9\-\s]/';
        } elseif ($this->meansEnglishAlphabet) {
            //The Alphabet means english alphabet.
            $pattern = '/[^0-9\-\s]/u';
        } else {
            //The Alphabet means each language's alphabet.
            $pattern = '/[^\p{N}\-\s]/u';
        }

        if ($value !== preg_replace($pattern, '', (string) $value)) {
            $label = __($attribute->getStoreLabel());
            $errors[] = __('"%1" must contain only numbers, hyphen and spaces.', $label);
        }

        return $errors;
    }

    /**
     * Check Unicode English function
     *
     * @return void
     */
    public function checkUnicodeEnglish()
    {
        $this->unicodeEnabled = preg_match('/\pL/u', 'a');
        $currentLocaleCode = $this->localeResolver->getLocale(); // en_US
        $languageCode = strstr($currentLocaleCode, '_', true);
        $this->meansEnglishAlphabet = in_array(
            $languageCode,
            ['ja', 'ko', 'zh']
        );
    }
}
