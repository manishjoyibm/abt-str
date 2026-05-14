<?php

namespace Abbott\CookiePreferences\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    private const TRUSTARC_CMI_ID = 'trustarc_cookie_preference/trustarc_cm/trustarc_cm_id';
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get CmId from MBO
     *
     * @param int $websiteId
     * @return string
     * @throws LocalizedException
     */
    public function getCmId($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::TRUSTARC_CMI_ID,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getWebsite($websiteId)->getCode()
        );
    }
}
