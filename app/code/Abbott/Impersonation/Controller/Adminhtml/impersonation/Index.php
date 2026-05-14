<?php

namespace Abbott\Impersonation\Controller\Adminhtml\impersonation;

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
        $resultPage->setActiveMenu('Abbott_Impersonation::impersonation');
        $resultPage->addBreadcrumb(__('Abbott'), __('Abbott'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Login As Customer Log'));
        $resultPage->getConfig()->getTitle()->prepend(__('Login As Customer Log'));

        return $resultPage;
    }
    
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Abbott_Impersonation::login_log');
    }
}
