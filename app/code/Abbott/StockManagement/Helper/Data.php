<?php

namespace Abbott\StockManagement\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\ScopeInterface;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

/**
 * Helper For stockmanagement
 */
class Data extends AbstractHelper
{
    public $stockRegistry;
    const XML_CONFIG_ENABLE = 'stock_management/configuration/enabled';

    protected $storeManager;

    protected $metabolicFactory;

    protected $customerSession;

    protected $timezoneInterface;

    protected $metabolicData;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        MetabolicFactory $metabolicFactory,
        TimezoneInterface $timezoneInterface,
        MetabolicData $metabolicData,
    ) {
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->metabolicFactory = $metabolicFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->metabolicData = $metabolicData;
        parent::__construct($context);
    }

     /**
      * Get the store Id
      * @return int
      */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
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
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Status of Module.
     * @return mixed
     */
    public function getConfigValue()
    {
        return $this->getModuleConfig(self::XML_CONFIG_ENABLE);
    }

     /* Get Status of Backorder.
     * @return mixed
     */
    public function checkStock($product)
    {
        return $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getBackorders();
    }


     /**
      * Get Stock details.
      * @return mixed
      */
    public function checkStockData($product, $data)
    {
        return $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getData($data);
    }


   /**
    * calculate threshold.
    * @return mixed
    */
    public function getThreshold($product, $isSubscription, $calcDiff = null)
    {
        $thresold = 0;
        if (isset($product->getData()['threshold']) && !empty($product->getData()['threshold'])) {
            $thresold = $product->getData()['threshold'];
        }
        $maxQty = 0;
        if ($isSubscription) {
            $maxQty = $product->getData()['cans_x_max_update'];
        } else {
            $maxQty = $this->checkStockData($product, 'max_sale_qty');
        }
        $productQty = $this->checkStockData($product, 'qty');
        $diff = $productQty - $thresold;

        if ($calcDiff != null) {
            if ($maxQty != 0) {
                $thresold = min($diff, $maxQty);
            } else {
                $thresold = $diff;
            }
        } else {
            if ($maxQty < $diff) {
                $thresold = $maxQty;
            }
        }
        return $thresold;
    }


    /**
     * Check can Metabolic products or not.
     */

    public function validateMetabolicOrderingProduct($customerEmail, $productSku)
    {
        $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
        $data['sku'] = $productSku;
        $data['customer_email'] = $customerEmail;
        if (($customerEmail) && ($this->metabolicData->ifExistingRecord($data))) {
                $metabolicDataResult = $this->metabolicData->ifExistingRecord($data);
            if (($metabolicDataResult['expiry_date'] >= $currentDate) && ($metabolicDataResult['qty'] > 0)) {
                return true;
            }


        }
        return false;
    }
}
