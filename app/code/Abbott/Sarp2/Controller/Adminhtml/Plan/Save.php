<?php

namespace Abbott\Sarp2\Controller\Adminhtml\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\PostDataProcessor\Composite as PostDataProcessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Framework\Validator\Exception as ValidatorException;


class Save extends Action
{
    public $planSaveAfterWebhook;
    public $_attributeRepository;
    public $_attributeOptionManagement;
    public $_option;
    public $_attributeOptionLabel;
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::plans';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param PostDataProcessor $postDataProcessor
     * @param PlanRepositoryInterface $planRepository
     * @param PlanInterfaceFactory $planFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        PostDataProcessor $postDataProcessor,
        PlanRepositoryInterface $planRepository,
        PlanInterfaceFactory $planFactory,
        DataObjectHelper $dataObjectHelper,
        \Abbott\Webhook\Model\PlanSaveAfter $planSaveAfterWebhook,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterface $attributeOptionLabel,
        \Magento\Eav\Model\Entity\Attribute\Option $option
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->postDataProcessor = $postDataProcessor;
        $this->planRepository = $planRepository;
        $this->planFactory = $planFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->planSaveAfterWebhook = $planSaveAfterWebhook;
    
        $this->_attributeRepository = $attributeRepository;
        $this->_attributeOptionManagement = $attributeOptionManagement;
        $this->_option = $option;
        $this->_attributeOptionLabel = $attributeOptionLabel;
        
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($requestData) {
            $entityData = $this->postDataProcessor->prepareEntityData($requestData);
            try {
                $plan = $this->performSave($entityData);
                $this->dataPersistor->clear('aw_sarp2_plan');
                $this->messageManager->addSuccessMessage(__('The plan was successfully saved.'));
                $this->planSaveAfterWebhook->execute();
                $back = $this->getRequest()->getParam('back');
                /** START add attribute option as per new plan only **/
                if($entityData['plan_id']== null) {
                    $this->addOptionForPlan($plan->getPlanId());
                }
                /** END add attribute option as per new plan only **/
                if ($back == 'edit') {
                    return $resultRedirect->setPath(
                        '*/*/' . $back,
                        [
                            'plan_id' => $plan->getPlanId(),
                            '_current' => true
                        ]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (ValidatorException $exception) {
                $this->addValidationMessages($exception);
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the plan.')
                );
            }

            $this->dataPersistor->set('aw_sarp2_plan', $entityData);

            if (isset($entityData['plan_id'])) {
                return $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'plan_id' => $entityData['plan_id'],
                        '_current' => true
                    ]
                );
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return PlanInterface
     */
    private function performSave($data)
    {
        $planId = isset($data['plan_id']) ? $data['plan_id'] : false;
        $plan = $planId
            ? $this->planRepository->get($planId)
            : $this->planFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $plan,
            $data,
            PlanInterface::class
        );
        return $this->planRepository->save($plan);
    }

    /**
     * Add validator exceptions message to message collection
     *
     * @param ValidatorException $exception
     * @return void
     */
    private function addValidationMessages(ValidatorException $exception)
    {
        $messages = $exception->getMessages();
        if (empty($messages)) {
            $messages = [$exception->getMessage()];
        }
        foreach ($messages as $message) {
            if (!$message instanceof Error) {
                $message = new Error($message);
            }
            $this->messageManager->addMessage($message);
        }
    }
    
    
    public function addOptionForPlan($planId) {
        try{           

            /** add option for new plan **/
            $attributeCode = 'plans';
            $attribute_id = $this->_attributeRepository->get('catalog_product', 'plans')->getAttributeId();

            /* new attribute option */
            $this->_option->setValue($planId);
            $this->_attributeOptionLabel->setStoreId(0);
            $this->_attributeOptionLabel->setLabel($planId);
            $this->_option->setLabel($planId);
            $this->_option->setStoreLabels([$this->_attributeOptionLabel]);
            $this->_option->setSortOrder(0);
            $this->_option->setIsDefault(false);
            $this->_attributeOptionManagement->add('catalog_product', $attribute_id, $this->_option);
        } catch (Exception $ex) {
            $this->messageManager->addExceptionMessage(
                    $ex,
                    __('Something went wrong while editing the plan.')
                );
        }
        /** END**/
    }
}
