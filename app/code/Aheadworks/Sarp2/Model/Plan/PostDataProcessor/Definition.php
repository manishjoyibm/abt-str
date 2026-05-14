<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class Definition
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class Definition implements ProcessorInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (isset($data['definition'])) {
            $definition = $data['definition'];

            if (empty($definition['total_billing_cycles'])) {
                $definition['total_billing_cycles'] = 0;
            }
            if ($definition['is_initial_fee_enabled'] == '') {
                $definition['is_initial_fee_enabled'] = false;
            }
            if ($definition['is_trial_period_enabled'] == '') {
                $definition['is_trial_period_enabled'] = false;
            }

            $isTrialEnabled = $this->booleanUtils->toBoolean($definition['is_trial_period_enabled']);
            if (!$isTrialEnabled) {
                $definition['trial_total_billing_cycles'] = 0;
                $data['trial_price_pattern_percent'] = 0;
            }

            $data['definition'] = $definition;
        }
        return $data;
    }
}
