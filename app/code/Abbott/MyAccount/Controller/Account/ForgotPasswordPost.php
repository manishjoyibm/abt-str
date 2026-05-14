<?php

namespace Abbott\MyAccount\Controller\Account;

use Abbott\CustomerTransistion\Helper\Data;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;

/**
 * ForgotPasswordPost controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordPost extends \Magento\Customer\Controller\Account\ForgotPasswordPost
{
    public $accountHelper;
    public $customerHelper;
    public $cookieMetadataFactory;
    public $cookieManager;
    public const FGP = '*/*/forgotpassword';

    /**
     * Construct function
     *
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     * @param Data $customerHelper
     * @param \Abbott\MyAccount\Helper\Data $accountHelper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        Data $customerHelper,
        \Abbott\MyAccount\Helper\Data $accountHelper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->accountHelper = $accountHelper;
        $this->customerHelper = $customerHelper;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        parent::__construct($context, $customerSession, $customerAccountManagement, $escaper);
    }

    /**
     * Execute function
     *
     * @return Redirect
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws ValidateException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!ValidatorChain::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(
                    __('The email address is incorrect. Verify the email address and try again.')
                );
                return $resultRedirect->setPath(self::FGP);
            }
            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath(self::FGP);
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We\'re unable to send the password reset email.')
                );
                return $resultRedirect->setPath(self::FGP);
            }
            $redirectUrl = $this->customerHelper->getFailureUrl();
            $cookieDomain = $this->accountHelper->getCookieRedirect();
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setDomain($cookieDomain);
            $publicCookieMetadata->setHttpOnly(false);
            $publicCookieMetadata->setSecure(true);
            $publicCookieMetadata->setSameSite('Lax');
            $this->cookieManager->setPublicCookie(
                'abt_msg',
                '{"type":"success","message":"' . $this->getSuccessMessage($email) . '"}',
                $publicCookieMetadata
            );
            return $resultRedirect->setUrl($redirectUrl);
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email.'));
            return $resultRedirect->setPath(self::FGP);
        }
    }
}
