<?php
namespace Abbott\GlobalOptOut\Model;

use Magento\Framework\Model\AbstractModel;

class Globalopt extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Globalopt::class);
    }
}
