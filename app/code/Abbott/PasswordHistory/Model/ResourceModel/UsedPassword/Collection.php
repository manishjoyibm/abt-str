<?php
namespace Abbott\PasswordHistory\Model\ResourceModel\UsedPassword;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Abbott\PasswordHistory\Model\UsedPassword;
use Abbott\PasswordHistory\Model\ResourceModel\UsedPassword as UsedPasswordResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(UsedPassword::class, UsedPasswordResource::class);
    }
}
