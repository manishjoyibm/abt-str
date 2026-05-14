<?php

namespace Abbott\CCPA\Controller\Adminhtml\Index;

use Abbott\CCPA\Model\Anonymous\DeactivateProfile;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Index extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    private const CUSTOMER_EDIT_PATH = 'customer/index/edit';

    /**
     * @var DeactivateProfile
     */
    protected DeactivateProfile $deactivateProfile;

    /**
     * @param Context $context
     * @param DeactivateProfile $deactivateProfile
     */
    public function __construct(
        Context           $context,
        DeactivateProfile $deactivateProfile
    ) {
        $this->deactivateProfile = $deactivateProfile;
        parent::__construct($context);
    }

    /**
     * Deactivate customer
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('customer_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->deactivateProfile->deactivateProfile($id);
        return $resultRedirect->setPath(self::CUSTOMER_EDIT_PATH, ['id' => $id]);
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Abbott_CCPA::deactivate_button');
    }
}
