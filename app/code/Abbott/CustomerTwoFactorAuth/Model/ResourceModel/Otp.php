<?php
namespace Abbott\CustomerTwoFactorAuth\Model\ResourceModel;

class Otp extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init("abbott_customer_two_fa", "entity_id");
    }
}
