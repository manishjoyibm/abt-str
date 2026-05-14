<?php
namespace Abbott\Targetbase\Model;

class Targetbase extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Abbott\Targetbase\Model\ResourceModel\Targetbase::class);
    }
}
