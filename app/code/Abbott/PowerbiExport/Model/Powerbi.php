<?php

namespace Abbott\PowerbiExport\Model;

class Powerbi extends \Magento\Framework\Model\AbstractModel
{
    public const ENTITY_ID = 'entity_id';
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Abbott\PowerbiExport\Model\ResourceModel\Powerbi::class);
    }
}
