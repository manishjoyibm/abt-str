<?php

namespace Abbott\MyAccount\Plugin\Adminhtml\Customer\Block\Edit;

use Abbott\MyAccount\Helper\LinkData;
use Magento\Customer\Model\CustomerFactory;

class ResetPasswordButton
{
    public $accountHelper;
    public $customerFactory;
    /**
     * Construct function
     *
     * @param LinkData $accountHelper
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        LinkData $accountHelper,
        CustomerFactory $customerFactory
    ) {
        $this->accountHelper = $accountHelper;
        $this->customerFactory = $customerFactory;
    }

    /**
     * AfterGetResetPasswordUrl function
     *
     * @param \Magento\Customer\Block\Adminhtml\Edit\ResetPasswordButton $subject
     * @param $result
     * @return mixed|string
     */
    public function afterGetResetPasswordUrl(
        \Magento\Customer\Block\Adminhtml\Edit\ResetPasswordButton $subject,
        $result
    ) {
        $customerId = $subject->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        $storeId = $customer->getStoreId();
        if ($this->accountHelper->getIsPasswordDisable($storeId) &&
            $this->accountHelper->getGigyaResetPasswordUrl($storeId)!="") {
            return $subject->getUrl(
                'myaccount/index/resetGigyaPassword',
                ['customer_id' => $customerId ]
            )
                ;
        }
        return $result;
    }
}
