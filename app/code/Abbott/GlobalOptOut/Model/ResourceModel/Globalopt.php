<?php
namespace Abbott\GlobalOptOut\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Globalopt extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('abbott_global_optout', 'id');
    }
}
