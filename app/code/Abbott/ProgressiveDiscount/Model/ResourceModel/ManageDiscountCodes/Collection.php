<?php

namespace Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'row_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\ProgressiveDiscount\Model\ManageDiscountCodes::class,
            \Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes::class
        );
    }
}
