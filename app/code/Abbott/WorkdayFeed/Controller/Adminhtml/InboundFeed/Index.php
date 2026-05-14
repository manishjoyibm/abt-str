<?php

namespace Abbott\WorkdayFeed\Controller\Adminhtml\InboundFeed;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Index action.
 */
class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Abbott_WorkdayFeed::inboundFeed';

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistorInterface;
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param DataPersistorInterface $dataPersistorInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistorInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataPersistorInterface = $dataPersistorInterface;
    }

    /**
     * Index action
     *
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Abbott_WorkdayFeed::abbott_inboundFeed');
        $resultPage->addBreadcrumb(__('Abbott'), __('Abbott'));
        $resultPage->addBreadcrumb(__('Manage Inbound Feed'), __('Manage Inbound Feed'));
        $resultPage->getConfig()->getTitle()->prepend(__('Feed Summary Report'));
        $this->dataPersistorInterface->clear('abbott_inboundFeed');

        return $resultPage;
    }
}
