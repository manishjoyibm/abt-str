<?php

namespace Abbott\MetabolicOrdering\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Abbott_MetabolicOrdering::save");
    }
    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        
        // Set the page title
        $id = (int) $this->getRequest()->getParam('entity_id');

        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Metabolic Ordering Record') : __('New Metabolic Ordering Record')
        );

        
        return $resultPage;
    }
}
