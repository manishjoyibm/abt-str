<?php

namespace Abbott\ProgressiveDiscount\Controller\Adminhtml\ManageDiscountCodes;

use Abbott\ProgressiveDiscount\Model\ManageDiscountCodesFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;

class Delete extends \Abbott\ProgressiveDiscount\Controller\Adminhtml\ManageDiscountCodes
{
    public $manageDiscountCodes;

    public const ADMIN_RESOURCE = 'Abbott_ProgressiveDiscount::delete';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ManageDiscountCodesFactory $manageDiscountCodes
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ManageDiscountCodesFactory $manageDiscountCodes
    ) {
        $this->manageDiscountCodes = $manageDiscountCodes;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('row_id');
        if ($id) {
            try {
                $model = $this->manageDiscountCodes->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Discount Code.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['row_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Discount Code to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
