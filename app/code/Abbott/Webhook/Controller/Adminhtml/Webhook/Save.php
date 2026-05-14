<?php

namespace Abbott\Webhook\Controller\Adminhtml\Webhook;

use Abbott\Webhook\Model\WebhookFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    protected $webhookFactory;

    public const WEBHOOK_ID = 'webhook_id';

    /**
     * Constructor
     *
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param WebhookFactory $webhookFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        WebhookFactory $webhookFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->webhookFactory = $webhookFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam(self::WEBHOOK_ID);
            $model = $this->webhookFactory->create()->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Webhook no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Webhook.'));
                $this->dataPersistor->clear('abbott_webhook_webhook');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [self::WEBHOOK_ID => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Webhook.'));
            }
            $this->dataPersistor->set('abbott_webhook_webhook', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                [self::WEBHOOK_ID => $this->getRequest()->getParam(self::WEBHOOK_ID)]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
