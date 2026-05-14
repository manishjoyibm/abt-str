<?php

namespace Abbott\Customerhistory\Model\ResourceModel;

/**
 * Customerhistory resource
 */
class Customerhistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('customerhistory', 'id');
    }
}
