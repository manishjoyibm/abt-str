<?php

namespace Abbott\ProgressiveDiscount\Controller\Adminhtml\ProgressiveDiscount;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Index extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    protected $resultPageFactory = false;

    protected $dataPersistorInterface;

    /**
     * Constructor
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
     * Execute
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Abbott_ProgressiveDiscount::manage_subscription');
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Subcription Month')));
        $this->dataPersistorInterface->clear('manage_subscription');
        return $resultPage;
    }
}
