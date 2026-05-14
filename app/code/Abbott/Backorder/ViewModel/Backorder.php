<?php

namespace Abbott\Backorder\ViewModel;

use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Backorder implements ArgumentInterface
{
    public const STATUS = 'additional_info/showdata/';
    public const BACKORDER = 'backorder';

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
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StockRegistryInterface $stockRegistry
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        StockStatusRepositoryInterface $stockStatusRepository,
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->stockStatusRepository = $stockStatusRepository;
    }

     /**
      * Get the store Id
      *
      * @return int
      */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
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
            (int)$product->getStore()->getWebsiteId()
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
     * Get value of Attribute.
     *
     * @param string $sku
     * @param string $attribute
     * @return mixed
     */
    public function getAttributeTextValue($sku, $attribute)
    {
        $product = $this->productRepository->get($sku);
        return $product->getResource()->getAttribute($attribute)->getFrontend()->getValue($product);
    }
}
