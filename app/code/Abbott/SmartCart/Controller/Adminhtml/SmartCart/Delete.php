<?php

namespace Abbott\SmartCart\Controller\Adminhtml\SmartCart;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Abbott\SmartCart\Model\SmartCartFactory;

class Delete extends Action
{
    /**
     * @var SmartCartFactory
     */
    private $smartCartFactory;

    /**
     * Delete constructor.
     *
     * @param SmartCartFactory $smartCartFactory
     * @param Action\Context $context
     */
    public function __construct(
        SmartCartFactory $smartCartFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->smartCartFactory = $smartCartFactory;
    }

    /**
     * Execute
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $smartCart = $this->smartCartFactory->create()->load($id);
        if ($smartCart->getId()) {
            $smartCart->delete();
            $this->messageManager->addSuccessMessage(__("This SmartCart has beed deleted"));
        } else {
            $this->messageManager->addErrorMessage(__("Could not delete this SmartCart"));
        }
        $this->_redirect("*/*/index");
    }
}
