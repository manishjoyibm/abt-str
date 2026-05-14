<?php

namespace Abbott\PowerbiExport\Model\ResourceModel\Powerbi;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = \Abbott\PowerbiExport\Model\Powerbi::ENTITY_ID;
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\PowerbiExport\Model\Powerbi::class,
            \Abbott\PowerbiExport\Model\ResourceModel\Powerbi::class
        );
    }
}
