<?php

namespace Abbott\GigyaIM\Model\ResourceModel\SsmCart;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\GigyaIM\Model\SsmCart::class,
            \Abbott\GigyaIM\Model\ResourceModel\SsmCart::class
        );
    }
}
