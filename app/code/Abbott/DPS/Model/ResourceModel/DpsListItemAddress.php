<?php
namespace Abbott\DPS\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DpsListItemAddress extends AbstractDb
{
    /**
     * Method _construct
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('abbott_dps_list_address', 'entity_id');
    }
}
