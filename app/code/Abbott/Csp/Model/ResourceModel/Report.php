<?php
namespace Abbott\Csp\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Report extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('abbott_csp_report', 'report_id');
    }
}
