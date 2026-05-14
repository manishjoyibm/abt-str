<?php

namespace Abbott\MetabolicOrdering\Model;

class Metabolic extends \Magento\Framework\Model\AbstractModel
{
    public const ENTITY_ID = 'entity_id';

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic::class);
    }
}
