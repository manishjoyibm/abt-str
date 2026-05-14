<?php

declare(strict_types=1);

namespace Abbott\MyAccount\Plugin\Controller\GiftCardAccount\Customer;

use Abbott\MyAccount\Helper\LinkData;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;

class IndexPlugin
{
    public $customerSession;
    public $dataHelper;
    public $url;
    public $response;
    public const XML_LAYOUT_NAME = "customer-account-navigation-gift-card-link";

    /**
     * Construct function
     *
     * @param Session $customerSession
     * @param LinkData $linkdataHelper
     * @param Http $response
     * @param UrlInterface $url
     */
    public function __construct(
        Session $customerSession,
        LinkData $linkdataHelper,
        Http $response,
        UrlInterface $url
    ) {
        $this->customerSession = $customerSession;
        $this->dataHelper = $linkdataHelper;
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
        if ($this->customerSession->isLoggedIn() && $this->dataHelper->isEnabled()
                && $this->dataHelper->checkForPagenotfound(self::XML_LAYOUT_NAME)) {
                $norouteUrl = $this->url->getUrl('noroute');
                $this->response->setRedirect($norouteUrl);
        }
    }
}
