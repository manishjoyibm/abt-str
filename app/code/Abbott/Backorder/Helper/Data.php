<?php

namespace Abbott\Backorder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper For ModuleStatus
 */
class Data extends AbstractHelper
{
    public const STATUS = 'additional_info/showdata/';
    public const BACKORDER = 'backorder';
  
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StockStatusRepositoryInterface
     */
    protected $stockStatusRepository;

    /**
     * construct function
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param StockRegistryInterface $stockRegistry
     * @param StockStatusRepositoryInterface $stockStatusRepository
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        StockStatusRepositoryInterface $stockStatusRepository
    ) {
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->stockStatusRepository = $stockStatusRepository;
        parent::__construct($context);
    }

     /**
      * Get the store Id
      *
      * @return int
      */
    
    public function getStoreId()
    {
        return (int)$this->storeManager->getStore()->getId();
    }


     /**
      * Get the Module Config
      *
      * @param string $path
      * @return mixed
      */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    /**
     * Get Status of Module.
     *
     * @param string $value
     * @return mixed
     */
    public function getStatus($value)
    {
        return $this->getModuleConfig(self::STATUS.$value);
    }

    /**
     * Get Status of Backorder.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function getBackorderStatus($product)
    {
        $stockdata['qty'] = $this->stockRegistry->getStockItem(
            $product->getId(),
            (int) $product->getStore()->getWebsiteId()
        )->getQty();
        $stockdata[self::BACKORDER] = $this->stockRegistry->getStockItem(
            $product->getId(),
            (int)$product->getStore()->getWebsiteId()
        )->getBackorders();
        $stockStatus = $this->stockStatusRepository->get((string)$product->getId());
        $productStockStatus = (int)$stockStatus->getStockStatus();
        if (($stockdata[self::BACKORDER] == 1 || $stockdata[self::BACKORDER] == 2)
        && $productStockStatus == 1 && $stockdata['qty'] <= 0) {
            $stockdata['status'] = 1;

        } else {
            $stockdata['status'] = 0;
        }
        return $stockdata['status'];
    }

   /**
     * Get status of Backorder Email module.
     *
     * @return bool True if backorder email notifications are enabled, false otherwise.
     */
    public function getBackourderEmailStatus(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'additional_info/backorder_email/enabled'
        );
    }

   
    /**
     * Retrieve selected store views for backorder email notifications.
     *
     * This method reads the configuration value from:
     * `additional_info/backorder_email/allowed_store_views`
     * and returns an array of store IDs. If no stores are selected,
     * it returns an empty array.
     *
     * @return array<int> List of selected store IDs.
     */
    public function getSelectedStores()
    {
        $selectedStores = $this->getModuleConfig('additional_info/backorder_email/allowed_store_views');
        return $selectedStores ? explode(',', $selectedStores) : [];
    }

    /**
     * Get the day threshold for backorder notifications.
     *
     * @return int Number of days after which backorder emails should be sent.
     */
    public function getDayThreshold(): int
    {
        return (int)$this->scopeConfig->getValue(
            'additional_info/backorder_email/days_threshold'
        );
    }

    /**
     * Get the email template ID for backorder notifications.
     *
     * @return string|null Template identifier or null if not configured.
     */
    public function getEmailTemplateId(): ?string
        {
            return $this->scopeConfig->getValue(
                'additional_info/backorder_email/email_template'
            );
        }

    /**
     * Get the configured email sender identity for backorder notifications.
     *
     * @param int|null $storeId Store ID for scope-specific configuration (optional).
     * @return string Sender identity code (e.g., 'sales', 'support').
     */
    public function getEmailSender(?int $storeId = null): string
        {
            return (string)$this->scopeConfig->getValue(
                'additional_info/backorder_email/sender'
            );
        }
    /**
     * Get status of Test feature
     *
     * @return bool True if test mode enabled, false otherwise.
     */
    public function getTestModeStatus(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            'additional_info/backorder_email/backorder_email_test_mode'
        );
    }
    /**
     * Get the emails for backorder notifications for test mode.
     *
     * @return string|null emails, comma separated or null if not configured.
     */
    public function getTestEmails(): array
    {
        $emails = $this->scopeConfig->getValue('additional_info/backorder_email/backorder_test_emails');
        return $emails ? array_map('trim', explode(',', $emails)) : [];
    }

}
