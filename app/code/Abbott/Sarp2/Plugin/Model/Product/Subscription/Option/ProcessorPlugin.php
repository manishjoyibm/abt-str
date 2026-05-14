<?php

namespace Abbott\Sarp2\Plugin\Model\Product\Subscription\Option;

class ProcessorPlugin
{
    const SUBSCRIPTION_START_DATE = 'sub_start_date';
    
    public function afterGetDetailedOptions(\Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor $subject, $details)
    {
        if (count($details) > 0) {
            array_unshift(
                $details,
                [
                    'label' => __('Subscription Start Date'),
                    'type' => self::SUBSCRIPTION_START_DATE,
                    'value' => date('Y-m-d')
                ]
            );
            foreach ($details as $key => $value) {
                if ($value['type'] == 'billing_cycle') {
                    $details[$key]['label'] = __('Billing Period');
                }
            }
        }
        
        return $details;
    }
}
