<?php

namespace Abbott\Checkout\Model\Attribute\Data\Plugin;

use Magento\Framework\Locale\Resolver;

class Text
{
    const INPUT_VALIDATION = 'input_validation';

    /**
     * Is PCRE is compiled with UTF-8 and Unicode support
     *
     * @var mixed
     **/
    protected mixed $unicodeEnabled;

    /**
     * The Alphabet means english alphabet.
     *
     * @var boolean
     */
    protected bool $meansEnglishAlphabet;

    /**
     * @var Resolver
     */
    private Resolver $localeResolver;

    /**
     * @param Resolver $localeResolver
     */
    public function __construct(
        Resolver $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    /**
     * @param \Magento\Eav\Model\Attribute\Data\Text $subject
     * @param array $result
     * @param array|string $value
     * @return array
     */
    public function afterValidateValue(
        \Magento\Eav\Model\Attribute\Data\Text $subject,
        $result,
        $value
    ) {
        $attribute = $subject->getAttribute();
        $validationRules = $attribute->getValidateRules();

        if (!empty($validationRules[self::INPUT_VALIDATION])) {
            switch ($validationRules[self::INPUT_VALIDATION]) {
                case "abt-name":
                    $validateNameResult = $this->validateName($attribute, $value);
                    $nameLength = count($validateNameResult);
                    $result = $this->getResult($result, $nameLength, $validateNameResult);
                    break;
                case "abt-company":
                    $validateCompanyResult = $this->validateCompany($attribute, $value);
                    $companyLength = count($validateCompanyResult);
                    $result = $this->getResult($result, $companyLength, $validateCompanyResult);
                    break;
                case "abt-mailing-address":
                    $validateAddressResult = $this->validateAddress($attribute, $value);
                    $addressLength = count($validateAddressResult);
                    $result = $this->getResult($result, $addressLength, $validateAddressResult);
                    break;
                case "abt-numeric-with-hyphen-spaces":
                    $validateNumHypResult = $this->validateNumHypSpaces($attribute, $value);
                    $numHypLength = count($validateNumHypResult);
                    $result = $this->getResult($result, $numHypLength, $validateNumHypResult);
                    break;
                default:
                    break;
            }
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
     * Validates value Name by attribute rules
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param string $value
     * @return array errors
     */
    public function validateName(\Magento\Eav\Model\Attribute $attribute, string $value): array
    {
        $errors = [];
        $this->checkUnicodeEnglish();

        if ($this->unicodeEnabled) {
            // POSIX named classes are not supported, use alternative a-zA-Z match
            $pattern = '/[^a-zA-Z\'\-\.\s]/';
        } elseif ($this->meansEnglishAlphabet) {
            //The Alphabet means english alphabet.
            $pattern = '/[^a-zA-Z\'\-\.\s]/u';
        } else {
            //The Alphabet means each language's alphabet.
            $pattern = '/[^\p{L}\'\-\.\s]/u';
        }

        if ($value !== preg_replace($pattern, '', (string) $value)) {
            $label = __($attribute->getStoreLabel());
            $errors[] = __('"%1" must contain only letters, spaces, apostrophes, hyphens and periods.', $label);
        }

        return $errors;
    }

    /**
     * Validates value Company by attribute rules
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param string $value
     * @return array errors
     */
    public function validateCompany(\Magento\Eav\Model\Attribute $attribute, string $value): array
    {
        $errors = [];
        $this->checkUnicodeEnglish();

        if ($this->unicodeEnabled) {
            // POSIX named classes are not supported, use alternative a-zA-Z match
            $pattern = '/[^a-zA-Z0-9\'\-\.\s]/';
        } elseif ($this->meansEnglishAlphabet) {
            //The Alphabet means english alphabet.
            $pattern = '/[^a-zA-Z0-9\'\-\.\s]/u';
        } else {
            //The Alphabet means each language's alphabet.
            $pattern = '/[^\p{L}\p{N}\'\-\.\s]/u';
        }

        if ($value !== preg_replace($pattern, '', (string) $value)) {
            $label = __($attribute->getStoreLabel());
            $errors[] = __(
                '"%1" must contain only letters, numbers,'.
                ' spaces, apostrophes, hyphens and periods.',
                $label
            );
        }

        return $errors;
    }

    /**
     * Validates value Address by attribute rules
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param string $value
     * @return array errors
     */
    public function validateAddress(\Magento\Eav\Model\Attribute $attribute, string $value): array
    {
        $errors = [];
        $this->checkUnicodeEnglish();

        if ($this->unicodeEnabled) {
            // POSIX named classes are not supported, use alternative a-zA-Z match
            $pattern = '/[^a-zA-Z0-9\#\,\-\.\s]/';
        } elseif ($this->meansEnglishAlphabet) {
            //The Alphabet means english alphabet.
            $pattern = '/[^a-zA-Z0-9\#\,\-\.\s]/u';
        } else {
            //The Alphabet means each language's alphabet.
            $pattern = '/[^\p{L}\p{N}\#\,\-\.\s]/u';
        }

        if ($value !== preg_replace($pattern, '', (string) $value)) {
            $label = __($attribute->getStoreLabel());
            $errors[] = __('"%1" must contain only letters, numbers, spaces, hash, hyphen, comma and period.', $label);
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
