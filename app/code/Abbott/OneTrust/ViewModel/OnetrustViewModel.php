<?php

namespace Abbott\OneTrust\ViewModel;

use Abbott\OneTrust\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class View Model OneTrust
 */
class OnetrustViewModel implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get SDK Endpoint Url
     *
     * @return string|null
     */
    public function getSdkEndpoint(): ?string
    {
        return $this->helper->getSdkEndpoint();
    }

    /**
     * Get Storage parameter value
     *
     * @return string|null
     */
    public function getSdkSetting(): ?string
    {
        return $this->helper->getSdkSetting();
    }

    /**
     * Get Storage container value
     *
     * @return string|null
     */
    public function getSdkStorageContainer(): ?string
    {
        return $this->helper->getSdkStorageContainer();
    }

    /**
     * Get SDK Js URL
     *
     * @return string|null
     */
    public function getSdkJsUrl(): ?string
    {
        return $this->helper->getSdkJsUrl();
    }

    /**
     * Get notice id value
     *
     * @param string $type
     * @return string
     */
    public function getNoticeId($type): string
    {
        return $this->helper->getNoticeId($type);
    }

    /**
     * Get OneTrust Module status
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->helper->isModuleEnabled();
    }

    /**
     * Check if SDK JS is Available or Down
     *
     * @return bool
     */
    public function isSdkJsAvailable(): bool
    {
        return $this->helper->isSdkJsAvailable();
    }
}
