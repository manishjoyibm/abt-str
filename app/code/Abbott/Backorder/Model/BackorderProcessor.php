<?php
namespace Abbott\Backorder\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Abbott\Backorder\Model\EmailLogFactory;
use Abbott\Backorder\Model\ResourceModel\EmailLog as EmailLogResource;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Abbott\Backorder\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Abbott\Backorder\Logger\Logger;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class BackorderProcessor
{  
    // Factory to create order collection instances
    protected $orderCollectionFactory;

    // Provides access to configuration values from the Magento scope
    protected $scopeConfig;

    // Used to build and send emails
    protected $transportBuilder;

    // Factory for creating email log model instances
    protected $emailLogFactory;

    // Resource model for saving and retrieving email log data
    protected $emailLogResource;

    // Provides date and time functionality
    protected $date;

    // Custom helper class for additional business logic
    protected $helper;

    // Repository interface for managing orders
    protected $orderRepository;

    // Provides URL generation and retrieval functionality
    public $urlInterface;

    // Repository interface for managing products
    public $productRepository;

    // Factory for creating region model instances
    public $regionFactory;

    // Factory for creating country model instances
    public $countryFactory;

    /**
     * Handles inline translation state for emails and other content
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * Manages store-related information and operations
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Represents the current store instance
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $store = null;

    /**
     * Provides access to stock information for products
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    // Factory to create product collection instances
    protected $productCollectionFactory;

    /**
     * Responsible for rendering customer address information
     * @var Renderer
     */
    protected $addressRenderer;

      /** @var Escaper */
    private $escaper;

    /**
     * @var Logger
     */
    private $logger;

    public const END_TD = "</td>";
    public const FONTS = "</td></tr><tr><td style='font-family: Georgia, Arial, sans-serif, serif, EmojiFont;'>";
    public const BILLING_FONTS = "</td></tr><tr><td style='font-family: Georgia, Arial, sans-serif,serif, EmojiFont;'>";
    public const STATUS = 'status';
    public const STORE_PHONE = 'general/store_information/phone';

       /**
     * @param OrderCollectionFactory  $orderCollectionFactory
     * @param ScopeConfigInterface    $scopeConfig
     * @param TransportBuilder        $transportBuilder
     * @param EmailLogFactory         $emailLogFactory
     * @param EmailLogResource        $emailLogResource
     * @param DateTime                $date
     * @param Data                    $helper
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface   $storeManager
     * @param Context                 $context
     * @param UrlInterface            $urlInterface
     * @param ProductRepositoryInterface $productRepository
     * @param ProductCollectionFactory $productCollectionFactory
     * @param RegionFactory           $regionFactory
     * @param CountryFactory          $countryFactory
     * @param InlineTranslationState  $inlineTranslation
     * @param StockRegistryInterface  $stockRegistry
     * @param Renderer                $addressRenderer
     * @param Escaper                 $escaper
     * @param Logger                  $logger
     * */

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        EmailLogFactory $emailLogFactory,
        EmailLogResource $emailLogResource,
        DateTime $date,
        Data $helper,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager,
        Context $context,
        UrlInterface $urlInterface,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        StateInterface $inlineTranslation,
        StockRegistryInterface $stockRegistry,
        Renderer $addressRenderer,
        Escaper $escaper,
        Logger $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->emailLogFactory = $emailLogFactory;
        $this->emailLogResource = $emailLogResource;
        $this->date = $date;
        $this->helper = $helper;
        $this->orderRepository        = $orderRepository;
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->stockRegistry = $stockRegistry;
        $this->addressRenderer = $addressRenderer;
        $this->escaper                = $escaper;
        $this->logger                 = $logger;
    }

   /**
     * Scan orders and send backorder notifications if needed.
     *
     * @return void
     */
    public function process(): void
    {
        $enabled = (bool)$this->helper->getBackourderEmailStatus();
        $daysThreshold = (int)$this->helper->getDayThreshold();
        $selectedStores = $this->helper->getSelectedStores();
        
        // Test mode controls
        $isTestMode = (bool)$this->helper->getTestModeStatus();
        $testEmailsRaw = (array)$this->helper->getTestEmails();
        $testEmails = $this->normalizeEmailList($testEmailsRaw);


        if (!$enabled) {
            $this->logger->info('[Backorder] Processor disabled via configuration.');
            return;
        }
        if ($daysThreshold <= 0) {
            $this->logger->warning('[Backorder] Invalid day threshold: {days}', ['days' => $daysThreshold]);
            return;
        }
         if (!$selectedStores) {
            $this->logger->error('[Backorder] Please select store ID .');
            return;
        }
        
        if ($isTestMode) {
            if (empty($testEmails)) {
                $this->logger->warning('[Backorder] Test Mode is enabled but no test emails are configured. Nothing will be processed.');
                return;
            }
            $this->logger->info('[Backorder] Test Mode enabled — restricting to configured emails.', ['emails' => $testEmails]);
        }

        
        // >>> Updated: compute the exact target calendar day (UTC) <<<
        
        // "Now" in UTC as timestamp using Magento's DateTime
        $nowTs = strtotime($this->date->gmtDate()); // e.g., 2025-12-15 10:12:07 UTC -> timestamp

        // Target day = (today - N days) as a date string in UTC
        $targetTs   = $nowTs - ($daysThreshold * 86400); // 86400 seconds in a day
        $targetDate = $this->date->gmtDate('Y-m-d', $targetTs); // e.g., "2025-12-13"

        // Build full-day bounds for the target calendar day (UTC)
        $dateStart = $targetDate . ' 00:00:00';
        $dateEnd   = $targetDate . ' 23:59:59';
        
        $this->logger->info('[Backorder] Checking orders from '. $dateStart.' To  '. $dateEnd);

        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter(self::STATUS, ['in' => ['backordered', 'partially_shipped']])
            // ONLY orders created on the exact target day
            ->addFieldToFilter('created_at', ['from' => $dateStart, 'to' => $dateEnd])
            ->addFieldToFilter('store_id', ['in' => $selectedStores]);
                        
        // In Test Mode, filter collection to only the configured emails
        if ($isTestMode && !empty($testEmails)) {
            $orders->addFieldToFilter('customer_email', ['in' => $testEmails]);
        }

        $itemCount = 0;

        foreach ($orders as $order) {
            $orderId = (int)$order->getId();
            if ($orderId <= 0) {
                continue;
            }

            if ($this->alreadySent($orderId)) {
                continue;
            }

            $hasBackOrderItems = false;
            foreach ($order->getAllItems() as $item) {
                // Support both method names for safety.
                $qtyBackordered = 0.0;
                if (method_exists($item, 'getQtyBackOrdered')) {
                    $qtyBackordered = (float)$item->getQtyBackOrdered();
                } elseif (method_exists($item, 'getQtyBackordered')) {
                    $qtyBackordered = (float)$item->getQtyBackordered();
                }
                if ($qtyBackordered > 0) {
                    $itemCount++;
                    $hasBackOrderItems = true;
                    break;
                }
            }

            if (!$hasBackOrderItems) {
                continue;
            }

            /**
             * Check Domin from config
             */
            $customerEmail = $order->getCustomerEmail();

            // If Test Mode is enabled and the order wasn't pre-filtered by collection (defensive check)
            if ($isTestMode && !in_array(strtolower(trim($customerEmail)), $testEmails, true)) 
            {
                // Not in the test list — skip
                continue;
            }

            try {
                $this->sendNotification($orderId);
            } catch (\Throwable $e) {
                // Log and continue to next order
                $this->logger->error(
                    '[Backorder] Failed to send notification.',
                    ['order_id' => $orderId, 'error' => $e->getMessage()]
                );
                continue;
            }

            try {
                $this->markAsSent($orderId);
            } catch (\Throwable $e) {
                $this->logger->error(
                    '[Backorder] Failed to mark as sent.',
                    ['order_id' => $orderId, 'error' => $e->getMessage()]
                );
                continue;
            }
        }
        // Summary (log only; no echo).
        $this->logger->info('[Backorder] Total backordered items processed: {count}', ['count' => $itemCount]);
    }

    
    /**
     * Normalize email list (trim + lowercase, filter empties)
     *
     * @param array $emails
     * @return array
     */
    protected function normalizeEmailList(array $emails): array
    {
        $normalized = [];
        foreach ($emails as $email) {
            $e = strtolower(trim((string)$email));
            if ($e !== '') {
                $normalized[] = $e;
            }
        }
        return $normalized;
    }

    /**
     * Check if an email notification was already sent for this order.
     *
     * @param int $orderId
     * @return bool
     */
    protected function alreadySent($orderId)
    {
        $emailLog = $this->emailLogFactory->create();
        $this->emailLogResource->load($emailLog, $orderId, 'order_id');
        return (bool)$emailLog->getId();
    }

    /**
     * Record that a notification has been sent.
     *
     * @param int $orderId
     * @return void
     * @throws \Exception
     */
    protected function markAsSent($orderId)
    {
        $emailLog = $this->emailLogFactory->create();
        $emailLog->setData([
            'order_id' => (int)$orderId,
            'sent_at' => $this->date->gmtDate()
        ]);
        $this->emailLogResource->save($emailLog);
    }

     /**
     * Perform backorder order
     *
     */
    public function sendNotification($orderId)
    {

         // Template ID (fallback if not configured)
        $templateId = $this->helper->getEmailTemplateId() ?: 'backorder_email';
        
        // Sender email config
        $sender     = $this->helper->getEmailSender();

        $orderData = $this->orderRepository->get($orderId);
        $incrementId = $orderData->getIncrementId();
        $createdAt = $orderData->getCreatedAtFormatted(1);
        $store = $orderData->getStore();
       // $this->setStore($store);
        $storeId = $orderData->getStoreId();
        $orderItems = "";
        foreach ($orderData->getAllItems() as $item) {
            $prodName = $item->getName();
            $sku = $item->getSku();
            $qty = $item->getQtyOrdered();
            $price = $item->getPrice();
            $itemStatus = $item->getStatus();
            $orderItems .= "<tr><td style='vertical-align: top; padding: 10px; border-top: 1px solid #cccccc;
font-family: Georgia, serif, sans-serif; padding-left: 20px;'>"
    . "<strong>" . $prodName . "</strong><br/>" 
    . $sku 
    . ($item->getStatus() == 'Backordered' ? "<br/><span style='color: red;'>" . $itemStatus . "</span>" : "")
    . self::END_TD;
    
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
                  'url' => '',
                  'storePhone' => $storePhone,
                  'formattedShippingAddress' => $this->getFormattedShippingAddress($orderData),
                  'formattedBillingAddress' => $this->getFormattedBillingAddress($orderData),
                ];

        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
        ->setTemplateIdentifier($templateId)
        ->setTemplateOptions(
            [
                 'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                 'store' =>  $this->helper->getStoreId(),
             ]
        )->setTemplateVars(
            $templateVars
        )->setFromByScope(
            $sender,
             $this->helper->getStoreId()
        )->addTo(
            $receiverEmail
        )->getTransport();
        $transport->sendMessage();
    }

    /* Render shipping address into html.
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