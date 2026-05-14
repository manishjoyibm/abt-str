<?php
namespace Abbott\Targetbase\Model\ResourceModel;

class Targetbase extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('apollo_targetbase', 'entity_id');
    }
}
