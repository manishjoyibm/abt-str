<?php

namespace Abbott\Customerhistory\Model;

use Magento\Framework\Exception\CustomerhistoryException;

/**
 * Customerhistorytab customerhistory model
 */
class Customerhistory extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Abbott\Customerhistory\Model\ResourceModel\Customerhistory::class);
    }
}
