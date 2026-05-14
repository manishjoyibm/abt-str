<?php

namespace Abbott\Customerhistory\Model\ResourceModel\Customerhistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Abbott\Customerhistory\Model\Customerhistory::class,
            \Abbott\Customerhistory\Model\ResourceModel\Customerhistory::class
        );
    }
}
