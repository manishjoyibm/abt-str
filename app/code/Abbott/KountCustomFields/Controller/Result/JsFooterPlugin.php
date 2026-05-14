<?php

declare(strict_types=1);

namespace Abbott\KountCustomFields\Controller\Result;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;

class JsFooterPlugin extends \Magento\Theme\Controller\Result\JsFooterPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Put all javascript to footer before sending the response.
     *
     * @param Http $subject
     * @return void
     */
}
