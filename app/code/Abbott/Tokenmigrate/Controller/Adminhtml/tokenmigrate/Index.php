<?php

namespace Abbott\Tokenmigrate\Controller\Adminhtml\tokenmigrate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    public $resultPageFactory;
    /**
     * @var PageFactory
     */
    protected $resultPagee;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Abbott_Tokenmigrate::tokenmigrate');
        $resultPage->addBreadcrumb(__('Abbott'), __('Abbott'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Run Token Migrate Script'));
        $resultPage->getConfig()->getTitle()->prepend(__('Run Token Migrate Script'));
        return $resultPage;
    }
}
