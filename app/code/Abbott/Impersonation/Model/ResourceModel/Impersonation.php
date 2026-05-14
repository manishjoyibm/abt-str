<?php
namespace Abbott\Impersonation\Model\ResourceModel;

class Impersonation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('abbott_impersonation', 'login_id');
    }
}
