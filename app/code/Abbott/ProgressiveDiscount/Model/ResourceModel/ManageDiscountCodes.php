<?php

namespace Abbott\ProgressiveDiscount\Model\ResourceModel;

class ManageDiscountCodes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('manage_discount_rules', 'row_id');
    }
}
