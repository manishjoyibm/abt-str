<?php

namespace Abbott\WorkdayFeed\Controller\Adminhtml\InboundFeedLog;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var bool|PageFactory
     */
    protected bool|PageFactory $resultPageFactory = false;

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistorInterface;

    /**
     * Initialization
     *
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
     * Execute function
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Abbott_WorkdayFeed::abbott_inboundFeedLog');
        $resultPage->getConfig()->getTitle()->prepend((__('Inbound Feed Log')));
        $this->dataPersistorInterface->clear('abbott_inboundFeedLog');
        return $resultPage;
    }
}
