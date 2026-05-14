<?php

namespace Abbott\ProgressiveDiscount\Controller\Adminhtml\ManageDiscountCodes;

use Abbott\ProgressiveDiscount\Model\ManageDiscountCodesFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Abbott\ProgressiveDiscount\Controller\Adminhtml\ManageDiscountCodes
{
    public $manageDiscountCodes;
    public const ADMIN_RESOURCE = 'Abbott_ProgressiveDiscount::save';

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ManageDiscountCodesFactory $manageDiscountCodes
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ManageDiscountCodesFactory $manageDiscountCodes
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->manageDiscountCodes = $manageDiscountCodes;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('row_id');
        $model = $this->manageDiscountCodes->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This DiscountCode no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('manage_discount_rules', $model);
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit DiscountCode') : __('New DiscountCode'),
            $id ? __('Edit DiscountCode') : __('New DiscountCode')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Discount Codes'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ?
                __('Edit DiscountCode %1', $model->getId()) :
                __('New DiscountCodes')
        );
        return $resultPage;
    }
}
