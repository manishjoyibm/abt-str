<?php

namespace Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = \Abbott\MetabolicOrdering\Model\Metabolic::ENTITY_ID;
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\MetabolicOrdering\Model\Metabolic::class,
            \Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic::class
        );
    }
}
