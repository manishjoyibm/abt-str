<?php

namespace Abbott\Customerhistory\Block\Adminhtml\Edit;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveButton
{

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * Constructor
     *
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(
        AccountManagementInterface $customerAccountManagement
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
    }

    /**
     * After Plugin on getButtonData
     *
     * @param \Magento\Customer\Block\Adminhtml\Edit\SaveButton $subject
     * @param $result
     * @return array|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetButtonData(\Magento\Customer\Block\Adminhtml\Edit\SaveButton $subject, $result)
    {
        $customerId = $subject->getCustomerId();
        $canModify = !$customerId || !$this->customerAccountManagement->isReadonly($subject->getCustomerId());
        if ($canModify) {
            $result = [
                'label' => __('Save and Close'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save']],
                    'form-role' => 'save',
                ],
                'sort_order' => 80,
            ];
        }
        return $result;
    }
}
