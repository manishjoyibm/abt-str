<?php

namespace Abbott\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\Plan\Source\StartDateType;
use Laminas\Validator\StaticValidator;

/**
 * Class Validator
 * @package Abbott\Sarp2\Model\Plan
 */
class Validator extends \Aheadworks\Sarp2\Model\Plan\Validator
{
    /**
     * Returns true if and only if plan entity meets the validation requirements
     *
     * @param PlanInterface $plan
     * @return bool
     */
    public function isValid($plan)
    {
        $this->_clearMessages();

        if (!StaticValidator::execute($plan->getName(), 'NotEmpty')) {
            $this->_addMessages(['Name is required.']);
        }
        if (!$this->isPercentageRange($plan->getRegularPricePatternPercent())) {
            $this->_addMessages(
                ['Regular payment price percentage must be greater or equal to 1 and less or equal to 100.']
            );
        }
        if ($plan->getDefinition()->getIsTrialPeriodEnabled()
            && !$this->isPercentageRange($plan->getTrialPricePatternPercent())
        ) {
            $this->_addMessages(
                ['Trial payment price percentage must be greater or equal to 1 and less or equal to 100.']
            );
        }
        if (!StaticValidator::execute($plan->getPriceRounding(), 'NotEmpty')) {
            $this->_addMessages(['Price rounding is required.']);
        }
        if (!$this->isDefinitionDataValid($plan)) {
            return false;
        }
        if (!$this->isTitlesDataValid($plan)) {
            return false;
        }

        return empty($this->getMessages());
    }

    /**
     * Returns true if and only if plan definition data is correct
     *
     * @param PlanInterface $plan
     * @return bool
     */
    private function isDefinitionDataValid(PlanInterface $plan)
    {
        $definition = $plan->getDefinition();

        if ($definition->getTotalBillingCycles()
            && !$this->isNumeric($definition->getTotalBillingCycles())
        ) {
            $this->_addMessages(['Number of payments is not a number.']);
            return false;
        }
        if ($definition->getStartDateType() == StartDateType::EXACT_DAY_OF_MONTH) {
            if (!StaticValidator::execute($definition->getStartDateDayOfMonth(), 'NotEmpty')) {
                $this->_addMessages(['Day of month is required.']);
                return false;
            } elseif (!$this->isNumeric($definition->getStartDateDayOfMonth())) {
                $this->_addMessages(['Day of month is not a number.']);
                return false;
            } elseif (!$this->isGreaterThanZero($definition->getStartDateDayOfMonth())) {
                $this->_addMessages(['Day of month must be greater than 0.']);
                return false;
            }
        }
        if ($definition->getIsTrialPeriodEnabled()) {
            if (!StaticValidator::execute($definition->getTrialTotalBillingCycles(), 'NotEmpty')) {
                $this->_addMessages(['Number of trial payments is required.']);
                return false;
            } elseif (!$this->isNumeric($definition->getTrialTotalBillingCycles())) {
                $this->_addMessages(['Number of trial payments is not a number.']);
                return false;
            } elseif (!$this->isGreaterThanZero($definition->getTrialTotalBillingCycles())) {
                $this->_addMessages(['Number of trial payments must be greater than 0.']);
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if and only if plan titles data are correct
     *
     * @param PlanInterface $plan
     * @return bool
     */
    private function isTitlesDataValid(PlanInterface $plan)
    {
        $isAllStoreViewsDataPresents = false;
        $titleStoreIds = [];
        if ($plan->getTitles()) {
            foreach ($plan->getTitles() as $title) {
                if (!in_array($title->getStoreId(), $titleStoreIds)) {
                    array_push($titleStoreIds, $title->getStoreId());
                } else {
                    $this->_addMessages(['Duplicated store view in storefront descriptions found.']);
                    return false;
                }
                if ($title->getStoreId() == 0) {
                    $isAllStoreViewsDataPresents = true;
                }

                if (!StaticValidator::execute($title->getTitle(), 'NotEmpty')) {
                    $this->_addMessages(['Storefront title is required.']);
                    return false;
                }
            }
        }
        if (!$isAllStoreViewsDataPresents) {
            $this->_addMessages(
                ['Default values of storefront descriptions (for All Store Views option) aren\'t set.']
            );
            return false;
        }
        return true;
    }

    /**
     * Check if value is numeric
     *
     * @param int $value
     * @return bool
     */
    private function isNumeric($value)
    {
        return StaticValidator::execute($value, 'Regex', ['pattern' => '/^\s*-?\d*(\.\d*)?\s*$/']);
    }

    /**
     * Check if value greater than 0
     *
     * @param int $value
     * @return bool
     */
    private function isGreaterThanZero($value)
    {
        return StaticValidator::execute($value, 'GreaterThan', ['min' => 0]);
    }

    /**
     * Check if value is in the correct percentage range
     *
     * @param float $value
     * @return bool
     */
    private function isPercentageRange($value)
    {
        return StaticValidator::execute(
            $value,
            'Between',
            ['min' => 1, 'max' => 100, 'inclusive' => true]
        );
    }
}
