<?php
namespace Abbott\Targetbase\Model\ResourceModel;

class TargetbaseOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('apollo_targetbase_order', 'entity_id');
    }
}
