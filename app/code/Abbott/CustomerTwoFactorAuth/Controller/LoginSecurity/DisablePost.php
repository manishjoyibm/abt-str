<?php
namespace Abbott\CustomerTwoFactorAuth\Controller\LoginSecurity;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Framework\View\Result\PageFactory;
use Abbott\CustomerTwoFactorAuth\Helper\Data as DataHelper;

/**
 * Disable 2fa action controller.
 */
class DisablePost extends AbstractAccount implements HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AuthenticationInterface $authentication
     * @param DataHelper $helper
     * @param MessageManager $messageManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AuthenticationInterface $authentication,
        DataHelper $helper,
        MessageManager $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->authentication = $authentication;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
    }

    /**
     * Disable 2fa action handler.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $customerId = $this->helper->customerSession->getCustomer()->getId();
        $currentPassword = $this->getRequest()->getPost('current-password');

        try {
            $this->authentication->authenticate($customerId, $currentPassword);
        } catch (InvalidEmailOrPasswordException $e) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('Password is incorrect.'));
            $resultRedirect->setPath('*/*/disable');

            return $resultRedirect;
        }
        if (!$this->helper->isCustomerSecurityEnabled()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*');

            return $resultRedirect;
        }
        $customer = $this->helper->customerRepository->getById($customerId);
        $customer->setCustomAttribute(DataHelper::IS_ENABLE, 0);
        $this->helper->customerRepository->save($customer);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*');
        $this->messageManager->addSuccessMessage(__('Two Factor Authentication has been disabled.'));
        return $resultRedirect;
    }
}
