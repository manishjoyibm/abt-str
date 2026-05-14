<?php
namespace Abbott\AbbottReport\Controller\Adminhtml\Report;

use \Magento\Framework\Controller\ResultFactory;

class Gratis extends \Magento\Backend\App\Action
{

    /**
     * This function is used to create sales report
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
