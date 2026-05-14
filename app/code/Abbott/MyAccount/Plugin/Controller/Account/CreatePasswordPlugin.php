<?php

declare(strict_types=1);

namespace Abbott\MyAccount\Plugin\Controller\Account;

use Abbott\MyAccount\Helper\LikData;
use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;

class CreatePasswordPlugin
{
    public $accountHelper;
    public $response;
    public $url;
    /**
     * Construct function
     *
     * @param LikData $accountHelper
     * @param Http $response
     * @param UrlInterface $url
     */
    public function __construct(
        \Abbott\MyAccount\Helper\LinkData $accountHelper,
        Http $response,
        UrlInterface $url
    ) {
        $this->accountHelper = $accountHelper;
        $this->response = $response;
        $this->url = $url;
    }

    /**
     * BeforeExecute function
     *
     * @return void
     */
    public function beforeExecute()
    {
        if ($this->accountHelper->getIsPasswordDisable()) {
            $norouteUrl = $this->url->getUrl('noroute');
                    $this->response->setRedirect($norouteUrl);
        }
    }
}
