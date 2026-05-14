<?php

namespace Abbott\Salesrule\Model\ResourceModel\SalesRuleAddressCodeUsage;

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
            'Abbott\Salesrule\Model\SalesRuleAddressCodeUsage',
            'Abbott\Salesrule\Model\ResourceModel\SalesRuleAddressCodeUsage'
        );
    }
}
