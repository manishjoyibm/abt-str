<?php

declare(strict_types=1);

namespace Abbott\MyAccount\Plugin\Controller\Account;

use Abbott\MyAccount\Model\Config\Source\Action;

class IndexPlugin
{
    public $customerSession;
    public $accountHelper;
    public $dataHelper;
    public $helper;
    public $url;
    public $response;
    public const XML_LAYOUT_NAME = "customer-account-navigation-account-link";

    protected $storeManager;

    /**
     * Construct function
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Abbott\CustomerTransistion\Helper\Data $helper
     * @param \Abbott\MyAccount\Helper\Data $accountHelper
     * @param \Abbott\MyAccount\Helper\LinkData $linkdataHelper
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Abbott\CustomerTransistion\Helper\Data $helper,
        \Abbott\MyAccount\Helper\Data $accountHelper,
        \Abbott\MyAccount\Helper\LinkData $linkdataHelper,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->customerSession = $customerSession;
        $this->accountHelper = $accountHelper;
        $this->dataHelper = $linkdataHelper;
        $this->helper = $helper;
        $this->url = $url;
        $this->response = $response;
    }

    /**
     * Redirect Front controller
     *
     * @return void
     */
    public function beforeExecute()
    {
        if ($this->customerSession->isLoggedIn() && $this->dataHelper->isEnabled() &&
            $this->accountHelper->getRedirectConfig('is_login_redirect_enabled')) {
            $failureUrl = $this->helper->getFailureUrl();
            $profilePageUrl = $this->accountHelper->getRedirectConfig('aem_profile_page');
            if (!empty($profilePageUrl)) {
                $this->response->setRedirect($failureUrl . $profilePageUrl);
            } elseif ($this->dataHelper->checkForPagenotfound(self::XML_LAYOUT_NAME)) {
                $norouteUrl = $this->url->getUrl('noroute');
                $this->response->setRedirect($norouteUrl);
            }
        }
    }
}
