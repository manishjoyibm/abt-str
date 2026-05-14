<?php
namespace Abbott\PowerbiExport\Model\ResourceModel;

class Powerbi extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const MAIN_TABLE = 'powerbi_export';
    public const ENTITY_ID = 'entity_id';

   /**
    * @inheritDoc
    */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ENTITY_ID);
    }
}
