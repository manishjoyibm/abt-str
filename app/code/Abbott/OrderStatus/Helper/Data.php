<?php

namespace Abbott\OrderStatus\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper For OrderStatus
 */
class Data extends AbstractHelper
{
    public const XML_PATH_MODULE_ENABLE = 'status_setting/settings/enable';
    public const XML_PATH_STORES_ENABLE = 'status_setting/settings/store_view';
    public const XML_PATH_CRON_ENABLE = 'status_setting/cron/enable';
    public const XML_PATH_DAYS = 'status_setting/days/number';
    public const XML_PATH_PAYMENT_METHOD = 'status_setting/settings/payment_method';

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
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
      * Get the store ID
      *
      * @return int
      * @throws NoSuchEntityException
      */
    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get the Module Config
     *
     * @param string $path
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getModuleConfig(string $path): mixed
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Status of Module.
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getModuleEnable(): mixed
    {
        return $this->getModuleConfig(self::XML_PATH_MODULE_ENABLE);
    }

    /**
     * Get Status of cron.
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCronEnable(): mixed
    {
        return $this->getModuleConfig(self::XML_PATH_CRON_ENABLE);
    }

    /**
     * Get Number of days.
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getNumberDays(): mixed
    {
        return $this->getModuleConfig(self::XML_PATH_DAYS);
    }

    /**
     * Get Payment method applied.
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getPaymentMethod(): mixed
    {
        return $this->getModuleConfig(self::XML_PATH_PAYMENT_METHOD);
    }

    /**
     * Get applied on stores.
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getStoresApplied(): mixed
    {
        return $this->getModuleConfig(self::XML_PATH_STORES_ENABLE);
    }
}
