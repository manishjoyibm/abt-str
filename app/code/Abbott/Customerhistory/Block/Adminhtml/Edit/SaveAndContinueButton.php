<?php

namespace Abbott\Customerhistory\Block\Adminhtml\Edit;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveAndContinueButton
{
    /**
     * @var AccountManagementInterface
     */
    protected AccountManagementInterface $customerAccountManagement;

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
     * @param \Magento\Customer\Block\Adminhtml\Edit\SaveAndContinueButton $subject
     * @param $result
     * @return array|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetButtonData(\Magento\Customer\Block\Adminhtml\Edit\SaveAndContinueButton $subject, $result)
    {
        $customerId = $subject->getCustomerId();
        $canModify = !$customerId || !$this->customerAccountManagement->isReadonly($subject->getCustomerId());
        if ($canModify) {
            $result = [
                'label' => __('Save and Continue Edit'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit'],
                    ],
                ],
                'sort_order' => 90,
            ];
        }
        return $result;
    }
}
