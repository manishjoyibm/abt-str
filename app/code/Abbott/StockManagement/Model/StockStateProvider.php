<?php

declare(strict_types=1);

namespace Abbott\StockManagement\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Math\Division as MathDivision;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

class StockStateProvider extends \Magento\CatalogInventory\Model\StockStateProvider
{
    const BACKORDER = 4;
    const XML_CONFIG_ENABLE = 'stock_management/configuration/enabled';
    const MESSAGE = 'Please correct the quantity for some products.';
    const BRAND = 'Metabolics';
    const AVAILABLE_FOR_CALL = 1;
    const LEVEL = 'Level1';
    const GENERIC_MESSAGE = 'Please see error below and update your cart.';
    const XML_CONFIG_PRODUCTLINE_MSG = 'stock_management/configuration/product_message';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var MathDivision
     */
    protected $mathDivision;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var bool
     */
    protected $qtyCheckApplicable;

    protected $metabolicFactory;

    protected $customerSession;

    protected $metabolicData;

    /**
     * @param MathDivision $mathDivision
     * @param FormatInterface $localeFormat
     * @param ObjectFactory $objectFactory
     * @param ProductFactory $productFactory
     * @param bool $qtyCheckApplicable
     */
    public function __construct(
        MathDivision $mathDivision,
        FormatInterface $localeFormat,
        ObjectFactory $objectFactory,
        ProductFactory $productFactory,
        MetabolicFactory $metabolicFactory,
        CustomerSession $customerSession,
        MetabolicData $metabolicData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $qtyCheckApplicable = true
    ) {
        $this->mathDivision = $mathDivision;
        $this->localeFormat = $localeFormat;
        $this->objectFactory = $objectFactory;
        $this->productFactory = $productFactory;
        $this->metabolicFactory = $metabolicFactory;
        $this->customerSession = $customerSession;
        $this->metabolicData = $metabolicData;
        $this->scopeConfig = $scopeConfig;
        $this->qtyCheckApplicable = $qtyCheckApplicable;
        parent::__construct($mathDivision, $localeFormat, $objectFactory, $productFactory, $qtyCheckApplicable);
    }

    public function checkQuoteItemQty(
        StockItemInterface $stockItem,
        $qty,
        $summaryQty,
        $origQty = 0
    )
    {
        $result = $this->objectFactory->create();
        $result->setHasError(false);

        $qty = $this->getNumber($qty);
        $product = $this->productFactory->create();
        $product->load($stockItem->getProductId());
        $productQty = $stockItem->getData()['qty'];
        $threshold = $product->getThreshold();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $moduleEnabled = $this->scopeConfig->getValue(self::XML_CONFIG_ENABLE, $storeScope);
        $getProductLineMsg = $this->scopeConfig->getValue(self::XML_CONFIG_PRODUCTLINE_MSG, $storeScope);


        /**
         * Check qty for Metabolic Ordering
         */
        $customerEmailID = $this->customerSession->getCustomer()->getEmail();
        $sku = $product->getSku();
        if (($this->metabolicData->getLevelAttributeLabel($sku) == self::LEVEL) &&
            ($customerEmailID != null) &&
            ($product->getOrderOnCall() == self::AVAILABLE_FOR_CALL)) {
            $data['sku'] = $sku;
            $data['customer_email'] = $customerEmailID;
            $metabolicDataResult = $this->metabolicData->ifExistingRecord($data);
            if ($qty > $metabolicDataResult['qty']) {
                $result->setHasError(true)
                    ->setMessage(__('The most you may purchase is %1.', $metabolicDataResult['qty'] * 1))
                    ->setErrorCode('qty_max')
                    ->setQuoteMessage(self::MESSAGE)
                    ->setQuoteMessageIndex('qty');
                return $result;
            }
        }

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($stockItem->getIsQtyDecimal());
        if (!$stockItem->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = (int) $qty ?: 1;
            /**
             * Adding stock data to quote item
             */
            $result->setItemQty($qty);
            $result->setOrigQty((int)$this->getNumber($origQty) ?: 1);
        }

        $itemMinQty = $stockItem->getMinSaleQty();
        $itemMaxQty = $stockItem->getMaxSaleQty();
        $minMaxMsg = "Min " . $itemMinQty . " and Max " . $itemMaxQty . " Quantity required";
        if ($itemMinQty && $qty < $itemMinQty) {
            $result->setHasError(true)
                ->setMessage($minMaxMsg)
                ->setErrorCode('qty_min')
                ->setQuoteMessage($minMaxMsg)
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($moduleEnabled && $stockItem->getBackorders() == self::BACKORDER) {
            $maxQty = $stockItem->getMaxSaleQty();
            $calculatedQty =  $productQty - $threshold;
            if ($qty > $calculatedQty && $calculatedQty <= $maxQty) {
                if ($calculatedQty < 1) {
                    $displayQty = (float)$productQty;
                } else {
                    $displayQty = $calculatedQty;
                }
                $result->setHasError(true)
                    ->setMessage(__($getProductLineMsg, [$product->getName() , $displayQty]))
                    ->setErrorCode('qty_max')
                    ->setQuoteMessage(self::GENERIC_MESSAGE)
                    ->setQuoteMessageIndex('qty');
            }
            if ($calculatedQty > $maxQty && $qty > $maxQty) {
                $result->setHasError(true)
                    ->setMessage(__('The most you may purchase is %1.', $stockItem->getMaxSaleQty() * 1))
                    ->setErrorCode('qty_max')
                    ->setQuoteMessage(self::MESSAGE)
                    ->setQuoteMessageIndex('qty');
            }
            return $result;
        } else {
            if ($stockItem->getMaxSaleQty() && $qty > $stockItem->getMaxSaleQty()) {
                $result->setHasError(true)
                    ->setMessage(__('The most you may purchase is %1.', $stockItem->getMaxSaleQty() * 1))
                    ->setErrorCode('qty_max')
                    ->setQuoteMessage(self::MESSAGE)
                    ->setQuoteMessageIndex('qty');
                return $result;
            }
        }

        $result->addData($this->checkQtyIncrements($stockItem, $qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$stockItem->getManageStock()) {
            return $result;
        }
        if (!$stockItem->getIsInStock()) {
            $result->setHasError(true)
                ->setErrorCode('out_stock')
                ->setMessage(__('This product is out of stock.'))
                ->setQuoteMessage(__(self::GENERIC_MESSAGE))
                ->setQuoteMessageIndex('qty');
            $result->setItemUseOldQty(true);
            return $result;
        }

        /*To check stock with threshold*/

        if ($moduleEnabled && $stockItem->getBackorders() == self::BACKORDER &&
            $stockItem->getData()['is_in_stock'] && $threshold >= $productQty) {
            $result->setHasError(true)
                ->setErrorCode('out_stock')
                ->setMessage(__('This product is out of stock.'))
                ->setQuoteMessage(__(self::GENERIC_MESSAGE))
                ->setQuoteMessageIndex('qty');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (!$this->checkQty($stockItem, $summaryQty) || !$this->checkQty($stockItem, $qty)) {
            $message = __('The requested qty is not available');
            $result->setHasError(true)
                ->setErrorCode('qty_available')
                ->setMessage($message)
                ->setQuoteMessage(self::GENERIC_MESSAGE)
                ->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if ($stockItem->getQty() - $summaryQty < 0) {
                if ($stockItem->getProductName()) {
                    if ($stockItem->getIsChildItem()) {
                        $backOrderQty = $stockItem->getQty() > 0 ? ($summaryQty - $stockItem->getQty()) * 1 : $qty * 1;
                        if ($backOrderQty > $qty) {
                            $backOrderQty = $qty;
                        }

                        $result->setItemBackorders($backOrderQty);
                    } else {
                        $orderedItems = (int)$stockItem->getOrderedItems();

                        // Available item qty in stock excluding item qty in other quotes
                        $qtyAvailable = ($stockItem->getQty() - ($summaryQty - $qty)) * 1;
                        if ($qtyAvailable > 0) {
                            $backOrderQty = $qty * 1 - $qtyAvailable;
                        } else {
                            $backOrderQty = $qty * 1;
                        }

                        if ($backOrderQty > 0) {
                            $result->setItemBackorders($backOrderQty);
                        }
                        $stockItem->setOrderedItems($orderedItems + $qty);
                    }

                    if ($stockItem->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$stockItem->getIsChildItem()) {
                            $result->setMessage(
                                __(
                                    'We don\'t have as many "%1" as you requested, '
                                    . 'but we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        } else {
                            $result->setMessage(
                                __(
                                    'We don\'t have "%1" in the requested quantity, '
                                    . 'so we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        }
                    } elseif ($stockItem->getShowDefaultNotificationMessage()) {
                        $result->setMessage(
                            __('The requested qty is not available')
                        );
                    }
                }
            } else {
                if (!$stockItem->getIsChildItem()) {
                    $stockItem->setOrderedItems($qty + (int)$stockItem->getOrderedItems());
                }
            }
        }
        return $result;
    }
}
