<?php

namespace Abbott\ProgressiveDiscount\Model\Rule\Condition;

use Abbott\ProgressiveDiscount\Helper\Data;

class ProductAction
{

    public $helper;

    public $checkoutHelper;

    /**
     * Constructor
     *
     * @param Data $helper
     * @param \Abbott\Checkout\Helper\Data $checkoutHelper
     */
    public function __construct(
        Data $helper,
        \Abbott\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->helper = $helper;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * AfterValidate
     *
     * @param $subject
     * @param $result
     * @param $model
     * @return bool|mixed
     */
    public function afterValidate($subject, $result, $model)
    {
        $attrCode = $subject->getAttribute();
        if ($attrCode === 'quote_item_is_progressive') {
            $options = $model->getProduct()->getTypeInstance(true)->getOrderOptions($model->getProduct());
            if (!empty($options)) {
                $itemPlanId = (isset($options['aw_sarp2_subscription_plan'])) ?
                    $options['aw_sarp2_subscription_plan']['plan_id'] : '';
                if (!empty($itemPlanId) && $this->helper->getIsProgressive($itemPlanId)) {
                    $result = true;
                }
            }
        } elseif ($attrCode === 'quote_item_is_one_time') {
            $options = $model->getProduct()->getTypeInstance(true)->getOrderOptions($model->getProduct());
            if (!empty($options) && isset($options['aw_sarp2_subscription_plan'])) {
                return false;
            }
            $customerId = $model->getQuote()->getCustomerId();
            if (!empty($customerId)) {
                $result = true;
            }
            $token = $this->checkoutHelper->getXIdToken();
            if (!empty($token)) {
                $result = true;
            }
        }
        return $result;
    }
}
