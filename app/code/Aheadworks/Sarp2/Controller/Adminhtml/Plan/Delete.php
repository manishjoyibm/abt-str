<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Controller\Adminhtml\Plan;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Delete
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Plan
 */
class Delete extends Action
{
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
        PlanRepositoryInterface $planRepository
    ) {
        parent::__construct($context);
        $this->planRepository = $planRepository;
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
