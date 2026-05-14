<?php
namespace Abbott\MetabolicOrdering\Model\ResourceModel;

class Metabolic extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const MAIN_TABLE = 'metabolic_ordering';
    public const ENTITY_ID = 'entity_id';

   /**
    * @inheritDoc
    */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ENTITY_ID);
    }
}
