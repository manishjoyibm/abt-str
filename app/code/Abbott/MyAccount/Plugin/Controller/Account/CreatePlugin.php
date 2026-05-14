<?php

namespace Abbott\MyAccount\Plugin\Controller\Account;

use Abbott\MyAccount\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http;

class CreatePlugin
{
    public $customerSession;
    public $accountHelper;
    public $helper;
    public $response;
    /**
     * Construct function
     *
     * @param Session $customerSession
     * @param Data $accountHelper
     * @param \Abbott\CustomerTransistion\Helper\Data $helper
     * @param Http $response
     */
    public function __construct(
        Session $customerSession,
        Data $accountHelper,
        \Abbott\CustomerTransistion\Helper\Data $helper,
        Http $response
    ) {
        $this->customerSession = $customerSession;
        $this->accountHelper = $accountHelper;
        $this->helper = $helper;
        $this->response = $response;
    }

    /**
     * BeforeExecute function
     *
     * @return void
     */
    public function beforeExecute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $failureUrl = $this->helper->getFailureUrl();
            $loginPageUrl = $this->accountHelper->getRedirectConfig('aem_registration_page');
            if ($this->accountHelper->getRedirectConfig('is_login_redirect_enabled') &&
                    !empty($failureUrl) && !empty($loginPageUrl)) {
                $this->response->setRedirect($failureUrl . $loginPageUrl);
            }
        }
    }
}
