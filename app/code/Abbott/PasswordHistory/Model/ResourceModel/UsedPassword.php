<?php
namespace Abbott\PasswordHistory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Abbott\PasswordHistory\Api\Data\UsedPasswordInterface;

class UsedPassword extends AbstractDb
{
    const TABLE_NAME = 'customer_used_password';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, UsedPasswordInterface::ID);
    }
}
