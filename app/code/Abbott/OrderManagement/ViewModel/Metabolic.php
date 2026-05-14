<?php
namespace Abbott\OrderManagement\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Metabolic implements ArgumentInterface
{

    public $productRepository;
    public $helper;
    const STATUS = 'metabolic_settings/metabolic_config/brand_name';
    const ENABLED = 'metabolic_settings/metabolic_config/enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
    }

    /**
     * Get the store Id
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /*
     * Return module status
     */
    public function getMetabolicEnable()
    {
        return $this->scopeConfig->getValue(
            self::ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * get metabolic status
     *
     * @param order
     * @return flag
     */
    public function getMetabolicStatus($order)
    {
        $flag = 1;
        if ($this->getMetabolicEnable()) {
            foreach ($order->getAllItems() as $item) {
                $product = $this->productRepository->getById(
                    $item->getProductId(),
                    false,
                    $this->getStoreId()
                );
                $brand = $product->getData('brand');
                $brandValue = $this->scopeConfig->getValue(
                    self::STATUS
                );
                if ($brandValue == $brand) {
                    $flag = 0;
                }
            }
        }
        return $flag;
    }

    /**
     * get stock status
     *
     * @param order
     * @return flag
     */
    public function getStockStatus($order)
    {
        $flag = 1;
        if ($this->helper->getConfigValue()) {
            foreach ($order->getAllItems() as $item) {
                $product = $this->productRepository->getById(
                    $item->getProductId(),
                    false,
                    $this->getStoreId()
                );
                if ($this->helper->checkStock($product) == 4) {
                    $threshold = $product->getData('threshold');
                    if ($product->getData()['quantity_and_stock_status']['qty'] <= $threshold) {
                        $flag = 0;
                    }
                }
            }
        }
        return $flag;
    }
}
