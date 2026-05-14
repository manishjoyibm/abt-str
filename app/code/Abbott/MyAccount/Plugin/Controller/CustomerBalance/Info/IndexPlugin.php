<?php

declare(strict_types=1);

namespace Abbott\MyAccount\Plugin\Controller\CustomerBalance\Info;

use Abbott\MyAccount\Model\Config\Source\Action;

class IndexPlugin
{
    public $customerSession;
    public $dataHelper;
    public $url;
    public $response;
    const XML_LAYOUT_NAME = "customer-account-navigation-customer-balance-link";

    /**
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Abbott\MyAccount\Helper\LinkData $linkdataHelper
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Abbott\MyAccount\Helper\LinkData $linkdataHelper,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->customerSession = $customerSession;
        $this->dataHelper = $linkdataHelper;
        $this->url = $url;
        $this->response = $response;
    }

    /**
     * Redirect Front controller
     */
    public function beforeExecute()
    {
        if ($this->customerSession->isLoggedIn() && $this->dataHelper->isEnabled()
                && $this->dataHelper->checkForPagenotfound(self::XML_LAYOUT_NAME)) {
                $norouteUrl = $this->url->getUrl('noroute');
                $this->response->setRedirect($norouteUrl);
        }
    }
}
