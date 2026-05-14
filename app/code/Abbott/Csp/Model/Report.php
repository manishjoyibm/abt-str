<?php
namespace Abbott\Csp\Model;

use Magento\Framework\Model\AbstractModel;

class Report extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Abbott\Csp\Model\ResourceModel\Report::class);
    }
}
