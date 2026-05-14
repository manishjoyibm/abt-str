<?php
namespace Abbott\Csp\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    /**
     * Constructor
     *
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }


    function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Abbott_Csp::report');
        $resultPage->getConfig()->getTitle()->prepend(__('CSP Reports'));
        return $resultPage;
    }
}
