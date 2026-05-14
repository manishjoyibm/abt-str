<?php

namespace Abbott\ProgressiveDiscount\Controller\Adminhtml\ManageDiscountCodes;

use Abbott\ProgressiveDiscount\Model\ManageDiscountCodesFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    public $manageDiscountCodes;

    public const ADMIN_RESOURCE = 'Abbott_ProgressiveDiscount::save';

    protected $dataPersistor;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ManageDiscountCodesFactory $manageDiscountCodes
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ManageDiscountCodesFactory $manageDiscountCodes
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->manageDiscountCodes = $manageDiscountCodes;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('row_id');
            $model = $this->manageDiscountCodes->create()->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This DiscountCode no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the DiscountCode.'));
                $this->dataPersistor->clear('managediscountcodes');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['row_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the DiscountCode.')
                );
            }
            $this->dataPersistor->set('managediscountcodes', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['managediscountcodes_id' => $this->getRequest()->getParam('managediscountcodes_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
