<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Abbott\Sarp2\Controller\Adminhtml\Plan;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Delete
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Plan
 */
class Delete extends Action
{
    public $_attributeRepository;
    public $_attributeOptionManagement;
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::plans';

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @param Context $context
     * @param PlanRepositoryInterface $planRepository
     */
    public function __construct(
        Context $context,
        PlanRepositoryInterface $planRepository,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement
    ) {
        parent::__construct($context);
        $this->planRepository = $planRepository;
        
        $this->_attributeRepository = $attributeRepository;
        $this->_attributeOptionManagement = $attributeOptionManagement;
        
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $planId = (int)$this->getRequest()->getParam('plan_id');
        if ($planId) {
            try {
                $this->planRepository->deleteById($planId);
                
                /** **/
                $attribute_id = $this->_attributeRepository->get('catalog_product', 'plans')->getAttributeId();
            
                $options = $this->_attributeOptionManagement->getItems('catalog_product', $attribute_id);
                /* if attribute option already exists, remove it */
                foreach($options as $option) {
                  if ($option->getLabel() == $planId) {
                    $this->_attributeOptionManagement->delete('catalog_product', $attribute_id, $option->getValue());
                  }
                }

                $this->messageManager->addSuccessMessage(__('Plan was successfully deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('Plan could not be deleted.'));
        return $resultRedirect->setPath('*/*/');
    }
}
