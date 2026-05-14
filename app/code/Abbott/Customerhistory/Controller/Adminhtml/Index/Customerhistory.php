<?php

namespace Abbott\Customerhistory\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Layout;

class Customerhistory extends \Magento\Backend\App\Action
{
    public $customerhistoryFactory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Abbott\Customerhistory\Model\CustomerhistoryFactory $customerhistoryFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Abbott\Customerhistory\Model\CustomerhistoryFactory $customerhistoryFactory
    ) {
        $this->customerhistoryFactory = $customerhistoryFactory;
        parent::__construct($context);
    }

   /**
    * Execute
    *
    * @return Layout
    */
    public function execute()
    {
        try {
            $comment =  $this->getRequest()->getParam('comment');
            $adminUserId = $this->getRequest()->getParam('admin_user_id');
            $username = $this->getRequest()->getParam('admin_name');
            $customerId = $this->getRequest()->getParam('customer_id');
            if ($comment) {
                $customerHistory = $this->customerhistoryFactory->create();
                $customerHistory->setCustomerId($customerId)
                    ->setComments($comment)
                    ->setFlag('comments')
                    ->setAdminUserId($adminUserId)
                    ->setAdminUsername($username)
                    ->save();
                $this->messageManager->addSuccess(__('Comment has been added'));
            } else {
                $this->messageManager->addErrorMessage(__('No comment entered'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Comment has not been added'));
        }
        $path = 'customer/index/edit';
        return $this->resultRedirectFactory->create()->setPath($path, ['_current' => true, 'id' => $customerId]);
    }
}
