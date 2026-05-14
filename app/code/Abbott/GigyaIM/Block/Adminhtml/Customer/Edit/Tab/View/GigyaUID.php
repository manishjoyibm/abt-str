<?php

namespace Abbott\GigyaIM\Block\Adminhtml\Customer\Edit\Tab\View;

use Abbott\GigyaIM\Helper\Data as Helper;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo;

class GigyaUID extends \Magento\Backend\Block\Template
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * GigyaUID constructor.
     *
     * @param Context $context
     * @param GigyaConfig $config
     */
    public function __construct(
        Context $context,
        Helper $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Check if the Gigya is enabled for the customer website.
     *
     * @return int
     */
    public function isGigyaEnabledForWebsite()
    {
        $customer = $this->getCustomerFromParentBlock();
        if ($customer) {
            return $this->helper->isGigyaEnabledForWebsite($customer->getWebsiteId());
        }
        return 0;
    }

    /**
     * Extract the Gigya ID from the customer present in the parent block.
     *
     * @return bool|string
     */
    public function getGUIDFromParentBlock()
    {
        $customer = $this->getCustomerFromParentBlock();
        if ($customer) {
            $attribute = $customer->getCustomAttribute('gigya_uid');
            if ($attribute) {
                return $attribute->getValue();
            }
        }
        return false;
    }

    /**
     * Extract the current customer from the parent block.
     *
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCustomerFromParentBlock()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && $parentBlock instanceof PersonalInfo) {
            return $parentBlock->getCustomer();
        }
        return false;
    }
}
