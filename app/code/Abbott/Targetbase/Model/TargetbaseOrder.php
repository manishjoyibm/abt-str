<?php
namespace Abbott\Targetbase\Model;

use Abbott\Targetbase\Api\Data\TargetbaseOrderInterface;

class TargetbaseOrder extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Abbott\Targetbase\Model\ResourceModel\TargetbaseOrder::class);
    }
}
