<?php

namespace Abbott\OneTrust\ViewModel;

use Abbott\OneTrust\Helper\Api;
use Abbott\OneTrust\Model\OneTrust;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class View Model CallApi
 */
class CallApi implements ArgumentInterface
{
    /**
     * @var Api
     */
    protected $helperApi;

    /**
     * @var OneTrust
     */
    protected $oneTrust;

    /**
     * Abbott\OneTrust\Helper\Api
     *
     * @param Api $helperApi
     * @param OneTrust $oneTrust
     */
    public function __construct(
        Api $helperApi,
        OneTrust $oneTrust
    ) {
        $this->helperApi = $helperApi;
        $this->oneTrust = $oneTrust;
    }

    public function isModuleEnable()
    {
        return $this->helperApi->isEnabled();
    }

    /**
     * Get Preference Center Iframe Url
     * @param $email
     * @return string|null
     */
    public function getPreferenceCenterUrl($email): string|null
    {
        return $this->oneTrust->getPreferenceCenterUrl($email);
    }
}
