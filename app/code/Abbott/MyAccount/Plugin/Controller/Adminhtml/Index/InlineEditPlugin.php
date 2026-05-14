<?php

namespace Abbott\MyAccount\Plugin\Controller\Adminhtml\Index;

use Abbott\MyAccount\Helper\LinkData;
use Magento\Customer\Controller\Adminhtml\Index\InlineEdit;

class InlineEditPlugin
{

    const XML_PATH_DISABLE_CUSTOMER_REGISTRATION = 'customer/create_account/disable_customer_registration';

    /**
     *
     * @var LinkData
     */
    protected $helperData;

    /**
     * Construct function
     *
     * @param LinkData $helperData
     */
    public function __construct(
        LinkData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * BeforeExecute function
     *
     * @param InlineEdit $subject
     */
    public function beforeExecute(InlineEdit $subject)
    {
        $postItems = $subject->getRequest()->getParam('items', []);
        foreach (array_keys($postItems) as $customerId) {
            $storeId = (string) $postItems[$customerId]['website_id'];
            $isDisabledEmailEdit = $this->helperData->getEmailEditDisableFlag($storeId);
            if ($isDisabledEmailEdit) {
                unset($postItems[$customerId]['email']);
            }
        }
        $subject->getRequest()->setParam('items', $postItems);
    }
}
