<?php

namespace Abbott\PedialyteCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper For OrderStatus
 */
class Data extends AbstractHelper
{
    private const XML_PATH_MODULE_ENABLE = 'pedialyte_configuration/settings/enable';
    private const XML_PATH_SKU_LIST = 'pedialyte_configuration/settings/sku_list';
    private const XML_PATH_PROGRESS_BAR = 'pedialyte_configuration/settings/show_shipping_progress_bar';
    private const XML_PATH_GUEST_FEATURE_ENABLE = 'pedialyte_configuration/settings/guest_feature';

    private const XML_PATH_SAVINGS = 'pedialyte_cart_discount/discount_settings/enable_savings_display';
    private const XML_PATH_SAVINGS_LABEL = 'pedialyte_cart_discount/discount_settings/savings_label';

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
     * Get the store Id
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }
    /**
     * Get the Module Config
     * @param $path
     * @param int $storeId
     * @return mixed
     */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId()
        );
    }

    /**
     * Get Status of Module.
     * @return mixed
     */
    public function getModuleEnable()
    {
        return $this->getModuleConfig(self::XML_PATH_MODULE_ENABLE);
    }

    /**
     * Check Guest Feature Enable.
     * @return mixed
     */
    public function isGuestFeatureEnable()
    {
        return $this->getModuleConfig(self::XML_PATH_GUEST_FEATURE_ENABLE);
    }

    /**
     * Get SKU list.
     * @return mixed
     */
    public function getSKUList()
    {
        return $this->getModuleConfig(self::XML_PATH_SKU_LIST);
    }

    // Fetch MBO SKU list
    public function getMboSkuList()
    {
        $skuList = $this->getSKUList();
        return array_map('trim', explode(',', $skuList)); // Convert to array
    }

    /**
     * Show Shipping Progress Bar
     *
     * @return null|bool
     */
    public function isShowShippingProgressBar(): ?bool
    {
        return $this->getModuleConfig(self::XML_PATH_PROGRESS_BAR);
    }
    /**
     * Get enable value of feature
     * @return bool
     */
    public function isDiscountPriceEnable(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SAVINGS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get label of discount
     *
     * @return string
     */
    public function getDiscountLabel(): string
    {
        $label = $this->scopeConfig->getValue(self::XML_PATH_SAVINGS_LABEL, ScopeInterface::SCOPE_STORE);
        return $label?: 'Total Savings';
    }
}
