<?php
declare(strict_types=1);

namespace Abbott\Customerhistory\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Abbott Customer History Helper
 *
 * Provides utilities for:
 *  - Setting a short-lived "manual logout" cookie (secure, HttpOnly, SameSite).
 *  - Reading and deleting the manual logout cookie.
 *  - Resolving customer session lifetime with safe fallback.
 *
 * Design notes:
 *  - Uses AbstractHelper::scopeConfig for config reads (Magento best practice).
 *  - Cookie metadata is created via CookieMetadataFactory to ensure compliance.
 *  - SameSite is set only if supported by the current metadata object.
 */
class Data extends AbstractHelper
{
    /** Cookie name used to mark manual logout */
    public const MANUAL_LOGOUT_COOKIE = 'abbott_manual_logged_out';

    /** Cookie value used to mark manual logout */
    public const MANUAL_LOGOUT_COOKIE_VALUE = 'manual_logout';

    /** Cookie duration in seconds (short-lived marker) */
    public const MANUAL_LOGOUT_COOKIE_DURATION = 60; // 60 seconds = 1 minute

    /** Config path for customer session lifetime */
    private const XML_PATH_CUSTOMER_SESSION_LIFETIME = 'customer/session/lifetime';

    /** Config path for generic cookie lifetime (web) */
    private const XML_PATH_WEB_COOKIE_LIFETIME = 'web/cookie/cookie_lifetime';

    // Admin config path for customer token lifetime (minute)
    private const XML_PATH_CUSTOMER_TOKEN_LIFETIME = 'webapi/jwtauth/customer_expiration';

    /** constant for use of manual records of fallback */
    public const LIFE_CONST = 3600; // 3600 sec = 1 hour



    /**
     * XML paths for system config
     */
    public const XML_PATH_ENABLED          = 'customer_history/general/enabled';
    public const XML_PATH_MANUAL_ENABLED   = 'customer_history/general/manual_enabled';
    public const XML_PATH_SESSION_ENABLED  = 'customer_history/general/session_enabled';


    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    /**
     * Constructor.
     *
     * @param Context                 $context
     * @param StoreManagerInterface   $storeManager
     * @param CookieManagerInterface  $cookieManager
     * @param CookieMetadataFactory   $cookieMetadataFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Set a secure, HttpOnly, short-lived manual logout cookie.
     *
     * Returns true if the cookie is set (best effort). In environments where
     * cookies are suppressed (headers already sent, etc.), this returns false.
     *
     * @return bool
     */
    public function setManualLogoutCookie(): bool
    {
        try {
            // Public cookie metadata (sent to browser)
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setDuration(self::MANUAL_LOGOUT_COOKIE_DURATION)
                ->setPath('/')               // site-wide path
                ->setHttpOnly(true)          // not accessible via JS
                ->setSecure($this->isSecure()); // respect HTTPS

            // SameSite is not available on older versions; guard via method_exists
            if (method_exists($metadata, 'setSameSite')) {
                // Lax is generally safe for login/logout flows
                $metadata->setSameSite('Lax');
            }

            $this->cookieManager->setPublicCookie(
                self::MANUAL_LOGOUT_COOKIE,
                self::MANUAL_LOGOUT_COOKIE_VALUE,
                $metadata
            );

            // CookieManager doesn't provide direct status, assume success
            return true;
        } catch (\Throwable $e) {
            // Log the error and return false (do not expose server internals)
            $this->_logger->error(sprintf(
                'Failed to set manual logout cookie: %s',
                $e->getMessage()
            ));
            return false;
        }
    }

    /**
     * Get the manual logout cookie value (if present).
     *
     * @return string|null Returns the cookie value or null if not present.
     */
    public function getManualLogoutCookie(): ?string
    {
        try {
           $cookieValue =  $this->cookieManager->getCookie(self::MANUAL_LOGOUT_COOKIE);
           return $cookieValue;
        } catch (\Throwable $e) {
            $this->_logger->error(sprintf(
                'Failed to get manual logout cookie: %s',
                $e->getMessage()
            ));
            return null;
        }
    }

    /**
     * Delete the manual logout cookie.
     *
     * Returns true if delete call was issued; false if an error occurred.
     *
     * @return bool
     */
    public function deleteManualLogoutCookie(): bool
    {
        try {
            // Delete using the same path to ensure removal
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath('/')
                ->setHttpOnly(true)
                ->setSecure($this->isSecure());

            if (method_exists($metadata, 'setSameSite')) {
                $metadata->setSameSite('Lax');
            }

            $this->cookieManager->deleteCookie(self::MANUAL_LOGOUT_COOKIE, $metadata);
            return true;
        } catch (\Throwable $e) {
            $this->_logger->error(sprintf(
                'Failed to delete manual logout cookie: %s',
                $e->getMessage()
            ));
            return false;
        }
    }

    /**
     * Resolve the effective customer session lifetime (in seconds).
     *
     * Priority:
     *  1) customer/session/lifetime
     *  2) web/cookie/cookie_lifetime
     *  3) hardcoded fallback (3600 seconds)
     *
     * @return int Lifetime in seconds
     */
    public function getCustomerLifetime(): int
    {
        try {
            $lifetime = (int)$this->scopeConfig->getValue(
                self::XML_PATH_CUSTOMER_SESSION_LIFETIME,
                ScopeInterface::SCOPE_STORE
            );

            if ($lifetime <= 0) {
                $lifetime = (int)$this->scopeConfig->getValue(
                    self::XML_PATH_WEB_COOKIE_LIFETIME,
                    ScopeInterface::SCOPE_STORE
                );
            }

            return $lifetime > 0 ? $lifetime : self::LIFE_CONST; // default 1 hour
        } catch (\Throwable $e) {
            $this->_logger->error(sprintf(
                'Failed to resolve customer lifetime: %s',
                $e->getMessage()
            ));
            return self::LIFE_CONST;
        }
    }

    /**
     * Resolve the effective customer session lifetime (in minute).
     *
     * @return int Lifetime in minute
     */
    public function getJwtLifeTime(): int
    {
        try {
            $lifetime = (int)$this->scopeConfig->getValue(
                self::XML_PATH_CUSTOMER_TOKEN_LIFETIME,
                ScopeInterface::SCOPE_STORE
            );
            $lifetime = $lifetime * 60 ;

            return $lifetime > 0 ? $lifetime : self::LIFE_CONST; // default 1 hour
        } catch (\Throwable $e) {
            $this->_logger->error(sprintf(
                'Failed to resolve JWT lifetime: %s',
                $e->getMessage()
            ));
            return self::LIFE_CONST;
        }
    }

    /**
     * Determine if current store context is secure (HTTPS).
     *
     * @return bool
     */
    private function isSecure(): bool
    {
        try {
            $store = $this->storeManager->getStore();
            return (bool)$store->isCurrentlySecure();
        } catch (\Throwable $e) {
            // If store cannot be resolved, default to secure = true for safety
            $this->_logger->warning(sprintf(
                'Could not determine secure store context: %s',
                $e->getMessage()
            ));
            return true;
        }
       }
       
    /**
     * Get config value by path (global scope only)
     *
     * @param string $path
     * @return string|null
     */
    public function getConfigValue(string $path): ?string
    {
        $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_DEFAULT);
        return $value !== '' ? $value : null;
    }

    /**
     * Get Yes/No flag by path (global scope only)
     *
     * @param string $path
     * @return bool
     */
    public function isFlag(string $path): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * Check if feature is enabled
     */
    public function isEnabled(): bool
    {
        return $this->isFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Check if manual logout tracking is enabled
     */
    public function isManualEnabled(): bool
    {
        return $this->isFlag(self::XML_PATH_MANUAL_ENABLED);
    }

    /**
     * Check if session logout tracking is enabled
     */
    public function isSessionEnabled(): bool
    {
        return $this->isFlag(self::XML_PATH_SESSION_ENABLED);
    }

}