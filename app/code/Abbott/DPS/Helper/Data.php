<?php

namespace Abbott\DPS\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    /**
     * Config if module should be enabled
     */
    public const IS_ENABLED = "abbott_dps/general/enable";
    /**
     * Config for Enabling cron job
     */
    public const IS_CRON_ENABLED = "abbott_dps/general/cron_enable";
    /**
     * Config for File url
     */
    public const FILE_URL = "abbott_dps/general/file_url";
    /**
     * Config for error message on checkout
     */
    public const ERROR_MESSAGE = "abbott_dps/general/error_message";

    /**
     * Config for match percentage for name
     */
    public const PERCENTAGE_NAME = "abbott_dps/percentage_match/name";

    /**
     * Config for match percentage for street
     */
    public const PERCENTAGE_STREET = "abbott_dps/percentage_match/street";

    /**
     * Config for match percentage for city
     */
    public const PERCENTAGE_CITY = "abbott_dps/percentage_match/city";

    /**
     * Config for match percentage for zip
     */
    public const PERCENTAGE_ZIP = "abbott_dps/percentage_match/zip";

    /**
     * Check if feature enabled
     *
     * @param mixed|null $store
     * @return mixed
     */
    public function isEnabled(mixed $store = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::IS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check is CronEnabled
     *
     * @param mixed|null $store
     * @return mixed
     */
    public function isCronEnabled(mixed $store = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::IS_CRON_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get File URL
     *
     * @param mixed|null $store
     * @return mixed
     */
    public function getFileUrl(mixed $store = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::FILE_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Error Message
     *
     * @param mixed|null $store
     * @return mixed
     */
    public function getErrorMessage(mixed $store = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Name Percentage
     *
     * @param mixed|null $store
     * @return mixed
     */
    public function getNamePercentage(mixed $store = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::PERCENTAGE_NAME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get City Percentage
     *
     * @param mixed|null $store
     * @return mixed
     */
    public function getCityPercentage(mixed $store = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::PERCENTAGE_CITY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Street Percentage
     *
     * @param mixed|null $store
     * @return mixed
     */
    public function getStreetPercentage(mixed $store = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::PERCENTAGE_STREET,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Zip Percentage
     *
     * @param mixed|null $store
     * @return mixed
     */
    public function getZipPercentage(mixed $store = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::PERCENTAGE_ZIP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
