<?php

namespace Abbott\GlucernaOrders\Controller\Adminhtml\Managesubscription;

class Edit extends \Abbott\GlucernaOrders\Controller\Adminhtml\Managesubscription
{
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('managesubscription_id');
        $model = $this->_objectManager->create(\Abbott\GlucernaOrders\Model\Managesubscription::class);
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Plan no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('abbott_glucernaorders_managesubscription', $model);

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Plan') : __('New Plan'),
            $id ? __('Edit Plan') : __('New Plan')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Subscription Plan'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Subscription Plan') : __('New Subscription Plan')
        );
        return $resultPage;
    }
}
