<?php

declare(strict_types=1);

namespace Abbott\MyAccount\Plugin\Controller\Account;

use Abbott\MyAccount\Helper\Data;
use Magento\Customer\Controller\Account\Login;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Psr\Log\LoggerInterface;

class LoginPlugin
{
    public $customerSession;
    public $accountHelper;
    public $cookieManager;
    public $helper;
    public $response;
    protected $logger;

    /**
     * Construct function
     *
     * @param Session $customerSession
     * @param Data $accountHelper
     * @param CookieManagerInterface $cookieManager
     * @param \Abbott\CustomerTransistion\Helper\Data $helper
     * @param Http $response
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $customerSession,
        Data $accountHelper,
        CookieManagerInterface $cookieManager,
        \Abbott\CustomerTransistion\Helper\Data $helper,
        Http $response,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->accountHelper = $accountHelper;
        $this->cookieManager = $cookieManager;
        $this->helper = $helper;
        $this->response = $response;
        $this->logger = $logger;
    }

    /**
     * BeforeExecute function
     *
     * @param Login $subject
     * @return void
     */
    public function beforeExecute(Login $subject)
    {
        if (!$this->customerSession->isLoggedIn()) {
            $customerData = $this->cookieManager->getCookie('abt_usr');
            if ($customerData) {
                $this->accountHelper->removeCookie('abt_usr');
                $this->accountHelper->removeCookie('abt_sesCartKey');
                $this->accountHelper->removeCookie('abt_cartKey');
                $this->accountHelper->removeCookie('abt_asm');
                $this->accountHelper->removeCookie('abt_te');
                $this->accountHelper->removeCookie('abt_sgp');
                $this->accountHelper->removeCookie('abt_psrid');
            }
            $failureUrl = $this->helper->getFailureUrl();
            $loginPageUrl = $this->accountHelper->getRedirectConfig('aem_login_page');

            $this->logger->info("Failure URL : " . $failureUrl . " Loginpage URL " . $loginPageUrl);

            if (!empty($failureUrl) && !empty($loginPageUrl)) {
                $this->response->setRedirect($failureUrl . $loginPageUrl);
            }
        }
    }
}
