<?php

namespace Abbott\Checkout\Model\Entity\Attribute\Frontend\Plugin;

class FrontendPlugin
{
    const INPUT_VALIDATE = 'input_validation';

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend $subject
     * @param string $result
     * @return string
     */
    public function afterGetClass(
        \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend $subject,
        $result
    ) {
        $validateRules = $subject->getAttribute()->getValidateRules();
        if (!empty($validateRules[self::INPUT_VALIDATE])) {
            switch ($validateRules[self::INPUT_VALIDATE]) {
                case "abt-name":
                    $result = !empty($result) ? $result . ' ' . 'validate-abt-name' : 'validate-abt-name';
                    $result = $this->getLengthValidateClasses($validateRules, $result);
                    break;
                case "abt-company":
                    $result = !empty($result) ? $result . ' ' . 'validate-abt-company' : 'validate-abt-company';
                    $result = $this->getLengthValidateClasses($validateRules, $result);
                    break;
                case "abt-mailing-address":
                    $result = !empty($result) ? $result . ' ' . 'validate-abt-mailing-address' :
                        'validate-abt-mailing-address';
                    $result = $this->getLengthValidateClasses($validateRules, $result);
                    break;
                case "abt-numeric-with-hyphen-spaces":
                    $result = !empty($result) ? $result . ' ' . 'validate-abt-numeric-with-hyphen-spaces' :
                        'validate-abt-numeric-with-hyphen-spaces';
                    $result = $this->getLengthValidateClasses($validateRules, $result);
                    break;
                case "abt-zipcode":
                    $result = !empty($result) ? $result . ' ' . 'validate-abt-zipcode' : 'validate-abt-zipcode';
                    $result = $this->getLengthValidateClasses($validateRules, $result);
                    break;
                default:
                    break;
            }
        }

        return $result;
    }

    /**
     * @param array $validateRules
     * @param string $result
     * @return string
     */
    private function getLengthValidateClasses($validateRules, $result)
    {
        $classes = [];
        if (!empty($validateRules['min_text_length'])) {
            $classes[] = 'minimum-length-' . $validateRules['min_text_length'];
        }
        if (!empty($validateRules['max_text_length'])) {
            $classes[] = 'maximum-length-' . $validateRules['max_text_length'];
        }
        if (!empty($classes)) {
            $classes[] = 'validate-length';
        }

        $lengthClass = !empty($classes) ? implode(' ', array_unique(array_filter($classes))) : '';

        return !empty($lengthClass) ? $result . ' ' . $lengthClass : $result;
    }
}
