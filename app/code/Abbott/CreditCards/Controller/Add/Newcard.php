<?php

namespace Abbott\CreditCards\Controller\Add;

class Newcard extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $publicHash = $this->getRequest()->getParam('public_hash');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->initMessages();
        $resultPage->getLayout()->getBlock('cc.add.newcard')->setPublicHash($publicHash);
        return $resultPage;
    }
}
