<?php

namespace Abbott\Webhook\Controller\Adminhtml\Webhook;

use Abbott\Webhook\Model\WebhookFactory;
use \Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    protected $webhookFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param WebhookFactory $webhookFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WebhookFactory $webhookFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->webhookFactory = $webhookFactory;
        parent::__construct($context);
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('webhook_id');
        $model = $this->webhookFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Webhook no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Abbott'), __('Abbott'))
            ->addBreadcrumb(__('Webhook'), __('Webhook'));
        $resultPage->getConfig()->getTitle()->prepend(__('Webhooks'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ?
                __('Edit Webhook %1', $model->getId()) :
                __('New Webhook')
        );
        return $resultPage;
    }
}
