<?php

namespace Abbott\GigyaIM\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Store\Model\StoreManagerInterface;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    public const IS_GIGYA_FIELDS_EDITABLE = 'gigya_integration/gigya_settings/is_gigya_fields_editable';

    public const IS_GIGYA_ENABLED_WEBSITE = 'gigya_integration/gigya_settings/enable_gigya';
    public const IS_COGNITO_ENABLED_WEBSITE = 'cognito_integration/cognito_settings/enable_cognito';

    /**
     * @var state
     */
    protected $state;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountHelper
     */
    protected $accountHelper;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManagerInterface;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * Construct function
     *
     * @param Context $context
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManagerInterface
     * @param AccountHelper $accountHelper
     */
    public function __construct(
        Context $context,
        State $state,
        StoreManagerInterface $storeManager,
        AccountHelper $accountHelper,
        CookieManagerInterface $cookieManagerInterface,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->cookieManagerInterface = $cookieManagerInterface;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->accountHelper = $accountHelper;
        parent::__construct($context);
    }

    /**
     * Check if the Gigya fields are editable for the given website id
     *
     * @param int $websiteId
     * @return int
     * @throws LocalizedException
     */
    public function isGigyaFieldsEditable($websiteId = null)
    {
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $areaCode = null;
        }

        if ($areaCode == Area::AREA_ADMINHTML) {
            if (!$websiteId) {
                return 0;
            }
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            return $this->scopeConfig->getValue(
                self::IS_GIGYA_FIELDS_EDITABLE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteCode
            );
        }
        return $this->scopeConfig->getValue(self::IS_GIGYA_FIELDS_EDITABLE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Check if the Gigya is enabled for the given website id
     *
     * @param int $websiteId
     * @return int|mixed
     * @throws LocalizedException
     */
    public function isGigyaEnabledForWebsite($websiteId = null)
    {
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $areaCode = null;
        }

        if ($areaCode == Area::AREA_ADMINHTML) {
            if (!$websiteId) {
                return 0;
            }
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            return $this->scopeConfig->getValue(
                self::IS_GIGYA_ENABLED_WEBSITE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteCode
            );
        }
        return $this->scopeConfig->getValue(self::IS_GIGYA_ENABLED_WEBSITE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function isCognitoEnabledForWebsite($websiteId = null)
    {
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $areaCode = null;
        }

        if ($areaCode == Area::AREA_ADMINHTML) {
            if (!$websiteId) {
                return 0;
            }
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            return $this->scopeConfig->getValue(
                self::IS_COGNITO_ENABLED_WEBSITE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteCode
            );
        }
        return $this->scopeConfig->getValue(self::IS_COGNITO_ENABLED_WEBSITE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * SetCookie function
     *
     * @param $key
     * @param $value
     * @return null
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function setCookie($key, $value)
    {
        $cookieDomain = $this->accountHelper->getCookieRedirect();
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setDomain($cookieDomain);
        $publicCookieMetadata->setHttpOnly(false);
        $publicCookieMetadata->setSecure(true);
        $publicCookieMetadata->setSameSite('Lax');
        return $this->cookieManagerInterface->setPublicCookie(
            $key,
            $value,
            $publicCookieMetadata
        );
    }

    /**
     * Get Custom Cookies
     *
     * @param string $name
     * @return string|null
     */
    public function getCustomCookie($name)
    {
        return $this->cookieManagerInterface->getCookie($name);
    }
}
