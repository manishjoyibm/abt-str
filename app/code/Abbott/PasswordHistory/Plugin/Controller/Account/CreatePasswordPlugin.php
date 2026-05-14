<?php
declare(strict_types=1);

namespace Abbott\PasswordHistory\Plugin\Controller\Account;

use Magento\Customer\Controller\Account\CreatePassword;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Exception\InputException;
use Abbott\PasswordHistory\Helper\Config;

class CreatePasswordPlugin
{
    /** @var Session */
    private $session;

    /** @var PageFactory */
    private $resultPageFactory;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var ConfirmCustomerByToken */
    private $confirmByToken;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var RedirectFactory */
    private $resultRedirectFactory;

    /** @var Config */
    private $config;

    public function __construct(
        Session $customerSession,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        ConfirmCustomerByToken $confirmByToken,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        Config $config
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->confirmByToken = $confirmByToken;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->config = $config;
    }

    /**
     * @param CreatePassword $subject
     * @param \Closure $proceed
     * @return Redirect|Page
     */
    public function aroundExecute(CreatePassword $subject, \Closure $proceed)
    {
        /* Stop further execution and checks if password history feature is disabled */
        if (!$this->config->isEnabled()) {
            return $proceed();
        }

        $request = $subject->getRequest();
        $resetPasswordToken = (string) $request->getParam('token');
        $customerId = (int) $request->getParam('id');

        $isDirectLink = $resetPasswordToken !== '';
        if (!$isDirectLink) {
            $resetPasswordToken = (string) $this->session->getRpToken();
            $customerId = (int) $this->session->getRpCustomerId();
        }

        try {
            // == Core flow ==
            $this->accountManagement->validateResetPasswordLinkToken($customerId, $resetPasswordToken);
            $this->confirmByToken->resetCustomerConfirmation($customerId);

            $customer = $this->customerRepository->getById($customerId);
            $this->accountManagement->changeResetPasswordLinkToken($customer, $resetPasswordToken);

            if ($isDirectLink) {
                $this->session->setRpToken($resetPasswordToken);
                $this->session->setRpCustomerId($customerId);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/createpassword');

                return $resultRedirect;
            } else {
                /** @var Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()
                           ->getBlock('resetPassword')
                           ->setResetPasswordLinkToken($resetPasswordToken)
                           ->setRpCustomerId($customerId);

                return $resultPage;
            }

        } catch (InputException $e) {
            // == Special redirect for InputException ==
            /** @var Redirect $resultRedirect */
            $params = [
                'token' => $this->session->getRpToken(),
                'id' => $this->session->getRpCustomerId()
            ];

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/createpassword', ['_query' => $params]);
            return $resultRedirect;

        } catch (\Exception $e) {
            // == Original fallback for other exceptions ==
            $this->messageManager->addErrorMessage(__('Your password reset link has expired.'));

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/forgotpassword');
            return $resultRedirect;
        }
    }
}