<?php
namespace Abbott\CustomerTwoFactorAuth\Model\ResourceModel\Otp;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $idFieldName = 'entity_id';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Abbott\CustomerTwoFactorAuth\Model\Otp::class,
            \Abbott\CustomerTwoFactorAuth\Model\ResourceModel\Otp::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
