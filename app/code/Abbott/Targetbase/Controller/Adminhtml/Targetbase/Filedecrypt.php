<?php
namespace Abbott\Targetbase\Controller\Adminhtml\Targetbase;

class Filedecrypt extends \Magento\Backend\App\Action
{
    /**
     * This function helps to show the decrypted data
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
