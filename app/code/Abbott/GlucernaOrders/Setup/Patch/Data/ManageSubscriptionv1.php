<?php

declare(strict_types=1);

namespace Abbott\GlucernaOrders\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class ManageSubscriptionv1 implements DataPatchInterface
{
    public $glucernaOrdersModel;
    public function __construct(
        \Abbott\GlucernaOrders\Model\Managesubscription $glucernaOrdersModel
    ) {
        $this->glucernaOrdersModel = $glucernaOrdersModel;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $plans =[
            ['plan_code' => 'PLN_30', 'plan_name' => 'Individual 30 bottles', 'plan_label' => '',
                'plan_value' => 30, 'plan_price' => '45.00', 'plan_shipping_rate' => 0.00, 'is_trial_plan' =>
                0, 'is_default_plan' => 0, 'is_active_plan' => 1],
            ['plan_code' => 'PLN_30', 'plan_name' => 'Individual 6 bottles (Trial)', 'plan_label' =>
                'After 14 days, you\'ll receive 30 shakes for $45 <p class="cut">($55)</p> per month & free
shipping.', 'plan_value' => 6, 'plan_price' => '11,5', 'plan_shipping_rate' => 0.00, 'is_trial_plan' => 1,
                'is_default_plan' => 0, 'is_active_plan' => 1, 'trial_period' => 7],
            ['plan_code' => 'PLN_60', 'plan_name' => 'Family 12 Bottles (Trial)', 'plan_label' =>
                'After 14 days, you\'ll receive 60 shakes for $85 <p class="cut">($110)</p> per month & free
shipping.', 'plan_value' => 12, 'plan_price' => '22,10', 'plan_shipping_rate' => 0.00, 'is_trial_plan' => 1,
                'is_default_plan' => 0, 'is_active_plan' => 1, 'trial_period' => 7],
            ['plan_code' => 'PLN_60', 'plan_name' => 'Family 60 Bottles', 'plan_label' => '', 'plan_value' =>
                60, 'plan_price' => '85.00', 'plan_shipping_rate' => 0.00, 'is_trial_plan' => 0,
                'is_default_plan' => 0, 'is_active_plan' => 1]
        ];

        foreach ($plans as $plan) {
            $this->glucernaOrdersModel->addData($plan);
            $this->glucernaOrdersModel->save();
            $this->glucernaOrdersModel->unsetData();
        }
    }

    public static function getDependencies()
    {
         return [];
    }

    public function getAliases()
    {
         return [];
    }
}
