<?php


namespace Abbott\Salesrule\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class SalesRuleAddressCodeUsage extends AbstractModel implements IdentityInterface
{
    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Abbott\Salesrule\Model\ResourceModel\SalesRuleAddressCodeUsage');
    }

    /**
     * GetIdentities
     *
     * @return void
     */
    public function getIdentities()
    {
        // TODO: Implement getIdentities() method.
    }
}
