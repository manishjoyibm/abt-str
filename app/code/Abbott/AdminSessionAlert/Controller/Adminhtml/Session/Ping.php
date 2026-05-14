<?php
namespace Abbott\AdminSessionAlert\Controller\Adminhtml\Session;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Backend\Model\Auth\Session as Session;
class Ping extends Action
{
    protected $resultJsonFactory;
    protected $session;
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        SessionManagerInterface $session
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->session->setData('last_ping', time());
        return $this->resultJsonFactory->create()->setData(['success' => true, 'message' => 'Session Extended']);
    }

    /**
     * Added resource for access
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}


