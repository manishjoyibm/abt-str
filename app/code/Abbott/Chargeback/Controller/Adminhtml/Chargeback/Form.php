<?php

namespace Abbott\Chargeback\Controller\Adminhtml\Chargeback;

class Form extends \Abbott\Chargeback\Controller\Adminhtml\Chargeback
{
    public $chargebackFactory;
    public $_coreRegistry;
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Abbott\Chargeback\Model\ChargebackFactory $chargebackFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->chargebackFactory = $chargebackFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Form action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('chargeback_id');
        $model = $this->chargebackFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Chargeback no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('abbott_chargeback_log', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Chargeback'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getId() : __('Upload Chargeback File'));
        return $resultPage;
    }
}
