<?php

namespace Abbott\Salesrule\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class SalesRuleAddressCodeUsage extends AbstractDb
{
    /**
     * Construct function
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_rule_address_code_usage', 'entity_id');
    }
}
