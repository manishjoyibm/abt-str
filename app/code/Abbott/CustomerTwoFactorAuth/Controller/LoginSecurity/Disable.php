<?php
namespace Abbott\CustomerTwoFactorAuth\Controller\LoginSecurity;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Abbott\CustomerTwoFactorAuth\Helper\Data;

/**
 * Disable page and form controller.
 */
class Disable extends AbstractAccount implements HttpGetActionInterface
{
    /** @var PageFactory */
    protected $resultPageFactory;

    /**
     * @var \Abbott\CustomerTwoFactorAuth\Helper\Data
     */
    public $helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
    }

    /**
     * Disable page and form handler.
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->helper->isCustomerSecurityEnabled()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }
        if (!$this->helper->isModuleEnabled()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account');
            return $resultRedirect;
        }
        $resultPage = $this->resultPageFactory->create();
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('customer/loginsecurity');
        }
        return $resultPage;
    }
}
