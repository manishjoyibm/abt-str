<?php
namespace Abbott\CustomerTwoFactorAuth\Controller\LoginSecurity;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Abbott\CustomerTwoFactorAuth\Helper\Data as DataHelper;

class Index extends AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @param Context $context
     * @param DataHelper $helper
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        DataHelper $helper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Main Page of Account Security handler.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->helper->isModuleEnabled()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account');
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
