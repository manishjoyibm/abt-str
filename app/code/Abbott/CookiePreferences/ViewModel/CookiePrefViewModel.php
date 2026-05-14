<?php

namespace Abbott\CookiePreferences\ViewModel;

use Abbott\CookiePreferences\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class View Model CookiePreferences
 */
class CookiePrefViewModel implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get CmId
     *
     * @return string|null
     */
    public function getCmId(): ?string
    {
        return $this->helper->getCmId();
    }
}
