<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class View
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Subscription
 */
class View extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ProfileInterfaceFactory
     */
    private $profileFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileInterfaceFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProfileInterfaceFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->profileFactory = $profileFactory;
        $this->profileRepository = $profileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $profileId = (int)$this->getRequest()->getParam('profile_id');
        /** @var ProfileInterface $profile */
        $profile = $this->profileFactory->create();
        if ($profileId) {
            try {
                $profile = $this->profileRepository->get($profileId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while open the profile page.')
                );
            }
        }
        if ($profile->getProfileId()) {
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage
                ->setActiveMenu('Aheadworks_Sarp2::subscriptions')
                ->getConfig()->getTitle()->prepend(
                    '#' . $profile->getIncrementId()
                );
            return $resultPage;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
