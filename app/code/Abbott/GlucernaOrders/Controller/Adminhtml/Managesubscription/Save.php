<?php

namespace Abbott\GlucernaOrders\Controller\Adminhtml\Managesubscription;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    const MANAGESUBSCRIPTION_ID = "managesubscription_id";
    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $data = $this->validatedParams($data);

        if ($data) {
            $id = $this->getRequest()->getParam(self::MANAGESUBSCRIPTION_ID);

            $model = $this->_objectManager->create(
                \Abbott\GlucernaOrders\Model\ManagesubscriptionFactory::class
            )->create()->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Subscription Plan no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Subscription Plan.'));
                $this->dataPersistor->clear('abbott_glucernaorders_managesubscription');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [self::MANAGESUBSCRIPTION_ID => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Subscription Plan.')
                );
            }

            $this->dataPersistor->set('abbott_glucernaorders_managesubscription', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                [self::MANAGESUBSCRIPTION_ID => $this->getRequest()->getParam(self::MANAGESUBSCRIPTION_ID)]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function validatedParams($formData)
    {
        if (empty(trim($formData['plan_code'])) || empty(trim($formData['plan_value']))) {
            throw new LocalizedException(__('Enter the Plan code and try again.'));
        }
        $data = [];
        foreach ($formData as $key => $value) {
            $data[$key] = trim($value);
        }
        return $data;
    }
}
