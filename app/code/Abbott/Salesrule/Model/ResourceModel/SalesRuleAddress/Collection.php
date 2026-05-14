<?php

namespace Abbott\Salesrule\Model\ResourceModel\SalesRuleAddress;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Abbott\Salesrule\Model\SalesRuleAddress',
            'Abbott\Salesrule\Model\ResourceModel\SalesRuleAddress'
        );
    }
}
