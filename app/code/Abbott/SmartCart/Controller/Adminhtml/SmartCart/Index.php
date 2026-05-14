<?php

namespace Abbott\SmartCart\Controller\Adminhtml\SmartCart;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Phrase;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Construct function
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
     * @return Page
     */
    public function execute()
    {
        $manageEntity = __('Manage') . ' ' . $this->_getEntityTitle();
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb($this->_getEntityTitle(), $this->_getEntityTitle());
        $resultPage->addBreadcrumb($manageEntity, $manageEntity);
        $resultPage->getConfig()->getTitle()->prepend($manageEntity);
        return $resultPage;
    }

    /**
     * GetEntityTitle
     *
     * @return Phrase
     */
    public function _getEntityTitle()
    {
        return __("SmartCart");
    }
}
