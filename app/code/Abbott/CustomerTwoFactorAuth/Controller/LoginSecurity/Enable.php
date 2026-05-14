<?php
namespace Abbott\CustomerTwoFactorAuth\Controller\LoginSecurity;

use Abbott\CustomerTwoFactorAuth\Helper\Data as DataHelper;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Abbott\CustomerTwoFactorAuth\Api\OtpManagerInterface;
use Magento\Framework\Message\Manager as MessageManager;

/**
 * Enable page and form controller.
 */
class Enable extends AbstractAccount implements HttpGetActionInterface
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
     * @var OtpManagerInterface
     */
    protected $otpManager;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param DataHelper $helper
     * @param OtpManagerInterface $otpManager
     * @param PageFactory $resultPageFactory
     * @param MessageManager $messageManager
     */
    public function __construct(
        Context $context,
        DataHelper $helper,
        OtpManagerInterface $otpManager,
        PageFactory $resultPageFactory,
        MessageManager $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->otpManager = $otpManager;
        $this->messageManager = $messageManager;
    }
    /**
     * Enable page and form handler.
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->helper->isModuleEnabled()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account');
            return $resultRedirect;
        }
        if ($this->helper->isCustomerSecurityEnabled()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*');
                     $this->messageManager->addSuccessMessage(__('Two Factor Authentication has been enabled.'));
            return $resultRedirect;
        }
        // setup active menu
        $resultPage = $this->resultPageFactory->create();
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');

        if ($navigationBlock) {
            $navigationBlock->setActive('customer/loginsecurity');

        }
        return $resultPage;
    }
}
