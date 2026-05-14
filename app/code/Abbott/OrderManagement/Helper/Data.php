<?php

namespace Abbott\OrderManagement\Helper;

use Abbott\MyAccount\Helper\Data as AccountHelper;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order\CollectionFactory;
use Abbott\ProgressiveDiscount\Helper\Data as HelperData;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * Abbott OrderManagement Data Helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $urlInterface;
    public $date;
    public $productRepository;
    public $orderRepository;
    public $regionFactory;
    public $countryFactory;
    /**
     * @var \Abbott\ProgressiveDiscount\Helper\Data
     */
    public $helper;
    public $collectionFactory;
    /**
     *
     */
    public const XML_PATH_CANCELORDER = 'cancel_order_setting/general/module_enable';
    public const EMAIL_TEMPLATE = 'cancel_order_setting/order_cancel_email/cancel_template';
    public const EMAIL_SENDER = 'cancel_order_setting/order_cancel_email/sender';
    public const EMAIL_ENABLED = 'cancel_order_setting/order_cancel_email/enabled';
    public const HOME_URL = 'my_account/myAccount_redirect/redirect_failure_url';
    public const STORE_PHONE = 'general/store_information/phone';
    public const END_TD = "</td>";
    public const FONTS = "</td></tr><tr><td style='font-family: Georgia, Arial, sans-serif, serif, EmojiFont;'>";
    public const BILLING_FONTS = "</td></tr><tr><td style='font-family: Georgia, Arial, sans-serif,
serif, EmojiFont;'>";
    public const FEDEX_STD_OVERNIGHT_SHIPPING = 'fedex_STANDARD_OVERNIGHT';
    public const ENABLE_AMASTY_INDEXER = 'enable_amasty_indexer_setting/general/enable_amasty_indexer';

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    public $profileRepository;

     /**
      *
      * @var HelperData
      */
    protected $data;

    protected $snsOrder;
    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $store = null;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    protected $productCollectionFactory;

    /**
     * @var Renderer
     */
    protected $addressRenderer;


    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     * @param DateTime $date
     * @param ProductRepositoryInterface $productRepository
     * @param TransportBuilder $transportBuilder
     * @param OrderRepository $orderRepository
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param StateInterface $inlineTranslation
     * @param StockRegistryInterface $stockRegistry
     * @param Renderer $addressRenderer
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Aheadworks\Sarp2\Model\ResourceModel\Profile\Order\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        ProfileRepositoryInterface $profileRepository,
        HelperData $data,
        \Aheadworks\Sarp2\Model\Profile\Order $snsOrder,
        Renderer $addressRenderer
    ) {
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->date = $date;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->transportBuilder = $transportBuilder;
        $this->orderRepository = $orderRepository;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->stockRegistry = $stockRegistry;
        $this->profileRepository = $profileRepository;
        $this->helper = $data;
        $this->snsOrder = $snsOrder;
        $this->collectionFactory = $collectionFactory;
        $this->addressRenderer = $addressRenderer;
        parent::__construct($context);
    }


    /**
     * @param $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStoreByStoreId($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        return $this->setStore($store);
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function setStore(\Magento\Store\Api\Data\StoreInterface $store)
    {
        $this->store = $store;
        return $this->store;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        if (!$this->store) {
            $store = $this->storeManager->getStore();
            return $this->setStore($store);
        }
        return $this->store;
    }

    public function getOrderProfiles($orderid)
    {
        $orderProfiles = $this->collectionFactory->create();
        $orderProfiles->addFieldToFilter('order_id', ['eq' => $orderid]);
        return $orderProfiles;
    }

    /*
     * Return module status
     */
    public function getEnable()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CANCELORDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getFormAction($order)
    {
        return $this->urlInterface->getUrl('sales/order/cancel', ['order_id' => $order->getId(), '_secure' => true]);
    }

    public function getIsTrial($order)
    {
        $isTrial = false;
        if ($this->getStore()->getId() == AccountHelper::GLU_STORE_ID) {
            $orderItems = $order->getAllVisibleItems();
            foreach ($orderItems as $item) {
                $product = $this->productRepository->getById(
                    $item->getProductId(),
                    false,
                    $this->getStore()->getId()
                );
                if ($product->getData('allow_trial')) {
                    $isTrial = true;
                    break;
                }
            }
        }
        return $isTrial;
    }

    /*
     * Return buffer time
     */

    public function getTime()
    {
        return $this->scopeConfig->getValue(
            'cancel_order_setting/general/cancel_buffer_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getCurrentDate()
    {
        return $this->date->gmtDate();
    }

    public function getMailEnabled()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getCancelOrderTemplate()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getSenderEmail()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * Perform cancel order
     *
     */
    public function sendCancelNotification($orderId)
    {
        $orderData = $this->orderRepository->get($orderId);
        $incrementId = $orderData->getIncrementId();
        $createdAt = $orderData->getCreatedAtFormatted(1);
        $store = $orderData->getStore();
        $this->setStore($store);
        $storeId = $orderData->getStoreId();
        $orderItems = "";
        foreach ($orderData->getAllItems() as $item) {
            $prodName = $item->getName();
            $sku = $item->getSku();
            $qty = $item->getQtyOrdered();
            $price = $item->getPrice();
            $orderItems .= "<tr><td  style='vertical-align: top; padding: 10px; border-top: 1px solid
#cccccc; font-family: Georgia, serif, sans-serif; padding-left: 20px;'>".$prodName."<br/>".$sku
                .self::END_TD;
            $orderItems .= "<td style='vertical-align: top; padding: 10px; border-top: 1px solid #cccccc;
 font-family: Georgia, serif, sans-serif;'>".number_format($qty).self::END_TD;
            $orderItems .= "<td style='vertical-align: top; padding: 10px; border-top: 1px solid #cccccc;
 font-family: Georgia, serif, sans-serif;'>".$store->getBaseCurrency()->getCurrencySymbol().""
                .number_format($price, 2).self::END_TD;
            $orderItems .= "</tr>";
        }

        $subTotal = number_format(($orderData->getSubtotal()), 2);
        $tax = number_format(($orderData->getTaxAmount()), 2);
        $shippingAmt = number_format(($orderData->getShippingAmount()), 2);
        $grandTotal = number_format(($orderData->getGrandTotal()), 2);

        /** Billing Info **/
        $billingName = $orderData->getBillingAddress()->getFirstname().
            " ".$orderData->getBillingAddress()->getLastname();
        $billingStreet1 = $orderData->getBillingAddress()->getStreet()[0];
        $billingCity = $orderData->getBillingAddress()->getCity();
        $billingRegionId = $orderData->getBillingAddress()->getRegionId();
        $billingCountryId = $orderData->getBillingAddress()->getCountryId();
        $billingTel = $orderData->getBillingAddress()->getTelephone();
        $billingTelePhone = (trim($billingTel ?? "")) ? "T: $billingTel" : null;
        $billingRegion = $this->regionFactory->create()->load($billingRegionId)->getName();
        $billingCountry = $this->countryFactory->create()->load($billingCountryId)->getName();
        $billingInfo = "<table>";
        $billingInfo .= "<tr><td style='font-family: Georgia, Arial, sans-serif, serif, EmojiFont;'>"
            .$billingName.self::BILLING_FONTS.$billingStreet1.self::BILLING_FONTS.$billingRegion
            .self::BILLING_FONTS.$billingCity.self::BILLING_FONTS.$billingCountry."</td></tr>
                        <tr><td style='font-family: Georgia, Arial, sans-serif, serif, EmojiFont;'>"
            .$billingTelePhone."</td></tr></tr>";
        $billingInfo .= "</table>";


        /** Shipping Info **/
        $shippingName = $orderData->getShippingAddress()->getFirstname()." "
            .$orderData->getShippingAddress()->getLastname();
        $shippingStreet1 = $orderData->getShippingAddress()->getStreet()[0];
        $shippingCity = $orderData->getShippingAddress()->getCity();
        $shippingRegionId = $orderData->getShippingAddress()->getRegionId();
        $shippingCountryId = $orderData->getShippingAddress()->getCountryId();
        $shippingTel = $orderData->getShippingAddress()->getTelephone();
        $shippingTelPhone = (trim($shippingTel ?? "")) ? "T: $shippingTel" : null;
        $shippingRegion = $this->regionFactory->create()->load($shippingRegionId)->getName();
        $shippingCountry = $this->countryFactory->create()->load($shippingCountryId)->getName();
        $shippingInfo = "<table>";
        $shippingInfo .= "<tr><td style='font-family: Georgia, Arial, sans-serif, serif, EmojiFont;'>"
            .$shippingName.self::FONTS.$shippingStreet1.self::FONTS.$shippingRegion.self::FONTS
            .$shippingCity.self::FONTS.$shippingCountry."</td></tr>
                          <tr><td style='font-family: Georgia, Arial, sans-serif, serif, EmojiFont;'>"
            .$shippingTelPhone."</td></tr></tr>";
        $shippingInfo .= "</table>";

        $receiverEmail = [$orderData->getCustomerEmail()];
        $url = $this->scopeConfig->getValue(self::HOME_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $storePhone = $this->scopeConfig->getValue(
            self::STORE_PHONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $storeName = $store->getFrontendName();
        $templateVars = [
                  'store' => $store,
                  'orderIncrement_id'    => $incrementId,
                  'created_at_formatted' => $createdAt,
                  'orderItems' => $orderItems,
                  'orderSubTotal' => $subTotal,
                  'orderTax' => $tax,
                  'orderShippingAmount' => $shippingAmt,
                  'orderGrandTotal' => $grandTotal,
                  'orderBilling' => $billingInfo,
                  'orderShipping' => $shippingInfo,
                  'orderData' => $orderData,
                  'storeName' => $storeName,
                  'url' => $url,
                  'storePhone' => $storePhone,
                  'formattedShippingAddress' => $this->getFormattedShippingAddress($orderData),
                  'formattedBillingAddress' => $this->getFormattedBillingAddress($orderData),
                ];
        $template = $this->getCancelOrderTemplate();
        $sender = $this->getSenderEmail();
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
        ->setTemplateIdentifier($template)
        ->setTemplateOptions(
            [
                 'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                 'store' => $this->getStore()->getId(),
             ]
        )->setTemplateVars(
            $templateVars
        )->setFromByScope(
            $sender,
            $this->getStore()->getId()
        )->addTo(
            $receiverEmail
        )->getTransport();
        $transport->sendMessage();
    }

    public function getOrderStatus($status)
    {
        switch ($status) {
            case "Suspected Fraud":
                return "Processing";
            case "Suspected Fraud From Kount":
                return "Processing";
            case "Chargeback":
                return "Complete";
            case "Error":
                return "Processing";
            default:
                return $status;
        }
    }

    public function checkBackOrder($sku)
    {
        $product = $this->productRepository->get($sku);
        $stockitem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getBackorders();

        if (($stockitem == 1 || $stockitem == 2) &&
            empty(
                $this->stockRegistry->getStockItem(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                )->getQty()
            )
        ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * To get attribute label from value
     *
     * @param sku
     * @param attributeid
     * @return attributevalue
     */
    public function checkAttrValue($sku, $attribute)
    {
        $product = $this->productRepository->get($sku);
        return $product->getResource()->getAttribute($attribute)->getFrontend()->getValue($product);
    }

    /**
     * @param orderId
     * @param array $options
     * @return plan id
     */
    public function checkIsProgressiveAndBuyersRemorse($orderId)
    {
        try {
            $data = [];

            $sns = $this->snsOrder->getCollection()->addFieldToFilter('order_id', ['eq'=>$orderId]);

            $sns->join(
                ['profile'=>'aw_sarp2_profile'],
                "main_table.profile_id = profile.profile_id",
                [
                    'profile.profile_id','main_table.order_id'
                ]
            );
                $sns->join(
                    ['plan'=>'aw_sarp2_plan'],
                    "profile.plan_id = plan.plan_id",
                    [
                    'plan.is_progressive','plan.is_cancel_order'
                    ]
                );
            if (!empty($sns)) {
                $data['is_sns'] = true;
                foreach ($sns->getData() as $progressiveCancelData):
                    $data['is_progressive'] = $progressiveCancelData['is_progressive'] ?
                        $progressiveCancelData['is_progressive'] : "";
                    $data['is_cancel_order'] = $progressiveCancelData['is_cancel_order'] ?
                        $progressiveCancelData['is_cancel_order'] : "";
                    if (!empty($data['is_progressive'])) {  //condition to handle order with multiple profiles
                        return $data;
                    }
                endforeach;
            }
            return $data;
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
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
      * get Call To Order status
      *
      * @param order
      * @return flag
      */
    public function getOrderOnCall($order)
    {
        $flag = 1;
        foreach ($order->getAllItems() as $item) {
            $skuArray[] = $item->getSku();
        }
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*')
                ->addAttributeToFilter('status', '1')
                ->addAttributeToFilter('sku', ['in'=>$skuArray])
                ->addAttributeToFilter('order_on_call', 1)
                ->addStoreFilter($this->getStoreId());
        if ($productCollection->getData()) {
            $flag = 1;
        }
        return $flag;
    }

    /**
     * Get Amasty Grid Indexer status
     *
     * @return bool
     */
    public function isAmastyGridIndexerEnabled(): bool
    {
        $amastyIndexerStatus = $this->scopeConfig->getValue(
            self::ENABLE_AMASTY_INDEXER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (bool)$amastyIndexerStatus;
    }

    /**
     * Render shipping address into html.
     *
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedShippingAddress(Order $order): ?string
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * Render billing address into html.
     *
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedBillingAddress(Order $order): ?string
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }
}
