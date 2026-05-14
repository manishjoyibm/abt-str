<?php

namespace Abbott\MyAccount\Plugin\Block\CustomAttributeManagement;

class Form
{
    /**
     * Return array of user defined attributes
     *
     * @param \Magento\CustomAttributeManagement\Block\Form $subject
     * @param $result
     * @return mixed
     */
    public function afterGetUserDefinedAttributes(\Magento\CustomAttributeManagement\Block\Form $subject, $result)
    {
        unset($result['gigya_uid']);
        unset($result['user_type']);
        unset($result['ssm_order_flag']);
        return $result;
    }
}
