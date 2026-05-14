<?php

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Helper\Transport;
use Abbott\WorkdayFeed\Model\InboundFeedFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Xml\Parser;
use Abbott\Hartehanks\Model\HhPlaceOrderFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Directory\Model\RegionFactory as RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\CategoryRepository;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Abbott\Hartehanks\Model\Method\Logger as HhLogger;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Abbott\Hartehanks\Observer\RushAttributeSuccess;
use Abbott\Hartehanks\Model\ResourceModel\HhProcessingOrder;

class HartehankPlaceOrderSync extends \Magento\Framework\Model\AbstractModel
{
    public $regionFactory;
    public $jsonSerializer;
    public $profileCollectionFactory;
    public $hhFindOrderSync;
    public $groupRepository;
    public $productRepository;
    public $categoryRepository;
    /**
     * @var null|bool
     */
    public $rushCheck;
    public $successCounter;
    /**
     * @var int
     */
    public $failureCounter;
    public $abbottStore;
    public $glucernaStore;
    public $similacStore;
    public $abbottSuccessCounter;
    public $glucernaSuccessCounter;
    public $similacSuccessCounter;
    const COMPANY = 'company';

    const STATUS_PROCESSING = 'processing';

    const PLACE_ORDER_REQUEST = 'Place-Order-Request';

    const PLACE_ORDER_RESPONSE = 'Place-Order-Response';

    const ORDER_COLLECTION = 'Order-Collection';

    const REGION_ID = 'region_id';

    const STATUS_RETRY1 = 'retry1';

    const STATUS_RETRY2 = 'retry2';

    const STATUS_RETRY3 = 'retry3';

    const INCREMENT_ID = 'increment_id';

    const TIME_FORMAT = 'Y-m-d H:i:s';

    const TN_SAMPLE_CONFIG = 'tn_sample/general/enable';

    const MINUTES = 'minutes';

    public const STATE ='state';

    public const STATE_CANCELED = 'canceled';

    protected const SALES_PAYMENT_TRANSACTION_TABLE_NAME = 'sales_payment_transaction';

    private const STATUS_VOID = 'void';

    private const TXN_CHECK_CONFIG = 'hartehanks/hartehanks_findorder/txn_check';

    private const LOCK_ORDERS_CONFIG = 'hartehanks/hartehanks_placeorder/lock_orders';

    private const TOPIC_NAME = 'hh.placeorder.sync';

    private const PROCESS_VIA_QUEUE = 'hartehanks/hartehanks_placeorder/process_via_queue';

    protected $transportHelper;

    protected $inboundFeedFactory;

    protected $logger;

    protected $orderCollectionFactory;

    protected $parser;

    protected $hhPlaceOrder;

    protected $regionCollectionFactory;

    protected $resource;

    protected $metadataFormFactory;

    protected $entityResolver;

    protected $customerCollectionFactory;

    protected $storeManagerInterface;

    protected $hhLogger;

    protected $date;

    protected $customerRepository;

    protected $accountHelper;

    protected $ruleRepository ;

    protected $scopeConfig ;

    protected $coupon;

    protected $objectManager;

    protected $lockOrdersFlag;

    /**
     * @var PublisherInterface
     */
    protected PublisherInterface $publisher;

    public function __construct(
        Transport $transportHelper,
        InboundFeedFactory $inboundFeedFactory,
        LoggerInterface $logger,
        OrderCollectionFactory $orderCollectionFactory,
        Parser $parser,
        Json $jsonSerializer,
        HhPlaceOrderFactory $hhPlaceOrder,
        RegionFactory $regionFactory,
        ResourceConnection $resource,
        FormFactory $metadataFormFactory,
        EntityResolver $entityResolver,
        ProfileCollectionFactory $profileCollectionFactory,
        HartehankFindOrderSync $hhFindOrderSync,
        GroupRepositoryInterface $groupRepository,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        StoreManagerInterface $storeManagerInterface,
        HhLogger $hhLogger,
        DateTime $date,
        CustomerRepositoryInterface $customerRepository,
        \Magento\SalesRule\Model\Rule $ruleRepository,
        \Magento\SalesRule\Model\Coupon $coupon,
        ScopeConfigInterface $scopeConfig,
        AccountHelper $accountHelper,
        ObjectManagerInterface $objectManager,
        PublisherInterface $publisher
    ) {
        $this->transportHelper = $transportHelper;
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->regionFactory = $regionFactory;
        $this->parser = $parser;
        $this->jsonSerializer = $jsonSerializer;
        $this->hhPlaceOrder = $hhPlaceOrder;
        $this->resource = $resource;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->entityResolver = $entityResolver;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->hhFindOrderSync = $hhFindOrderSync;
        $this->groupRepository = $groupRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->hhLogger = $hhLogger;
        $this->date = $date;
        $this->customerRepository = $customerRepository;
        $this->ruleRepository  = $ruleRepository;
        $this->coupon = $coupon;
        $this->scopeConfig  = $scopeConfig;
        $this->rushCheck = null;
        $this->successCounter = 0;
        $this->failureCounter = 0;
        $this->abbottStore = 0;
        $this->glucernaStore = 0;
        $this->similacStore = 0;
        $this->abbottSuccessCounter = 0;
        $this->glucernaSuccessCounter = 0;
        $this->similacSuccessCounter = 0;
        $this->accountHelper = $accountHelper;
        $this->objectManager = $objectManager;
        $this->publisher = $publisher;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $expiryTime = strtotime(
            "-".$this->transportHelper->getSyncMinutes()." ".self::MINUTES,
            strtotime(date(self::TIME_FORMAT))
        );
        $endTime = $this->date->date(self::TIME_FORMAT, $expiryTime);
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(Transport::STATUS, ['in' => [self::STATUS_PROCESSING]]);
        $orderCollection->addFieldToFilter(['created_at', 'is_rush_order'], [['lteq' => $endTime], ['eq' => 1]]);
        $orderCollection->addFieldToFilter(self::STATE, ['in' => [self::STATUS_PROCESSING]]);
        $orderCollection->setPageSize($this->transportHelper->getOrderCollectionSize());
        $data = $orderCollection->getSelect()->__toString();
        $this->hhLogger->debug(self::ORDER_COLLECTION, $data);
        if ($orderCollection->count()) {
            $this->placeOrder($orderCollection);
        }
    }

    public function executeWithoutLimit($incrementId)
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(Transport::STATUS, ['in' => [self::STATUS_PROCESSING]]);
        $orderCollection->addFieldToFilter(self::INCREMENT_ID, ['in' => $incrementId]);
        $orderCollection->setPageSize($this->transportHelper->getOrderCollectionSize());
        $data = $orderCollection->getSelect()->__toString();
        $this->hhLogger->debug(self::ORDER_COLLECTION, $data);
        if ($orderCollection->count()) {
            return $this->placeOrder($orderCollection);
        }
    }

    public function placeRushOrder($orderId)
    {
        $this->rushCheck = true;
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('entity_id', ['eq' => $orderId]);
        $this->placeOrder($orderCollection);
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function placeOrder($orderCollection)
    {
        $orderString = '';
        $orderIds = [];
        $lockOrders = [];
        $txnCheck = $this->scopeConfig->getValue(
            self::TXN_CHECK_CONFIG,
            ScopeInterface::SCOPE_STORE,
            $this->storeManagerInterface->getStore()->getId()
        );
        foreach ($orderCollection as $order) {
            if ($order->getRetryCount() == 1) {
                $expiryTime = strtotime(
                    "-".$this->transportHelper->getRetryTimeLimitOne()." ".self::MINUTES,
                    strtotime(date(self::TIME_FORMAT))
                );
                $endTime = $this->date->date(self::TIME_FORMAT, $expiryTime);
                if ($order->getUpdatedAt() > $endTime) {
                    continue;
                }
            } elseif ($order->getRetryCount() == 2) {
                $expiryTime = strtotime(
                    "-".$this->transportHelper->getRetryTimeLimitTwo()." ".self::MINUTES,
                    strtotime(date(self::TIME_FORMAT))
                );
                $endTime = $this->date->date(self::TIME_FORMAT, $expiryTime);
                if ($order->getUpdatedAt() > $endTime) {
                    continue;
                }
            } elseif ($order->getRetryCount() == 3) {
                $expiryTime = strtotime(
                    "-".$this->transportHelper->getRetryTimeLimitThree()." ".self::MINUTES,
                    strtotime(date(self::TIME_FORMAT))
                );
                $endTime = $this->date->date(self::TIME_FORMAT, $expiryTime);
                if ($order->getUpdatedAt() > $endTime) {
                    continue;
                }
            }

            if ($txnCheck && $this->verifyOrderTxn($order->getId())) {
                continue;
            }

            $string = $this->getOrderQuery($order);
            if (!empty($string)) {
                $orderString .= $string;
            }

            $orderIds[] = $order->getId();

            $lockOrders[] = [
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId()
            ];
        }

        if (empty($orderString)) {
            return;
        }

        $this->lockOrdersFlag = $this->scopeConfig->getValue(
            self::LOCK_ORDERS_CONFIG,
            ScopeInterface::SCOPE_STORE,
            $this->storeManagerInterface->getStore()->getId()
        );

        if ($this->lockOrdersFlag) {
            $this->hhLogger->debug('HarteHanks Locked Order Ids : ', $lockOrders);
            $this->lockOrders($lockOrders);
        }

        $xmlStr = '<Orders>'.$orderString.'</Orders>';

        $xmlPostString = $this->transportHelper->getOrderXmlQuery(Transport::PLACEORDER_IDENTIFIER, $xmlStr);
        $this->hhLogger->debug(self::PLACE_ORDER_REQUEST, $xmlPostString);

        $processViaQueue = $this->scopeConfig->getValue(
            self::PROCESS_VIA_QUEUE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManagerInterface->getWebsite()->getId()
        );

        if ($processViaQueue) {
            $rawData = [
                'xmlPostString' => $xmlPostString,
                'orderIds' => $orderIds,
            ];
            $this->hhLogger->debug('HarteHanks : Publishing Orders to Queue. OrderIds: ', $orderIds);
            return $this->publisher->publish(self::TOPIC_NAME, $this->jsonSerializer->serialize($rawData));
        } else {
            $this->hhLogger->debug('HarteHanks : Processing Orders without Queue.', []);
            try {
                $this->sendOrdersToHH($xmlPostString, null, $orderCollection);
            } catch (\Exception $e) {
                $this->hhLogger->debug('HarteHanks API Failed. ', $e->getMessage());
            }
        }
    }

    private function getOrderQuery($order)
    {
        try {
            $xmlStr = null;
            $incrementId = $order->getIncrementId();
            $shippingMethod = $order->getShippingMethod();
            $subtotal = $order->getSubTotal();
            $shippingAmount = $order->getShippingAmount();
            $taxAmount = $order->getTaxAmount();
            $totalPrice = $order->getGrandTotal();

            $billingAddress = $order->getBillingAddress();
            $logData = [$incrementId, $billingAddress[self::REGION_ID], $billingAddress['region']];
            $this->hhLogger->debug('Billing_Address', $logData);
            $costCenter = $this->getCustomerCostCenter($order->getCustomerId());

            $shippingAddress = $order->getShippingAddress();
            $recipientStreet =  $shippingAddress->getStreet();
            $recipientAddress1 = htmlspecialchars($recipientStreet[0]);
            $recipientAddress2 = empty($recipientStreet[1]) ? '' : htmlspecialchars($recipientStreet[1]);
            $shippingAddressArray = $this->getAddressDetails($shippingAddress->getData());
            list($recipient_firstName,
                $recipient_lastName, ,
                $recipient_zip, ,
                $recipient_city,
                $recipientCountry,
                $recipientPhone) = $shippingAddressArray;
            $recipientCompany = isset($shippingAddress[self::COMPANY])?
                htmlspecialchars($shippingAddress[self::COMPANY] ?? "") : null;
            $logData = [$incrementId, $shippingAddress[self::REGION_ID], $shippingAddress['region']];
            $this->hhLogger->debug('Shipping_Address', $logData);
            $recipientRegion = $this->getRegionCode($shippingAddress[self::REGION_ID]);

            $orderAttributesData = $this->getOrderAttributesData($order);
            if (isset($orderAttributesData['additional_fedex'])) {
                $shippingMethodCode = ($orderAttributesData['additional_fedex']!= null) ?
                    $this->getShippingCode($orderAttributesData['additional_fedex']) :
                    $this->getShippingCode($shippingMethod);
            } else {
                $shippingMethodCode = $this->getShippingCode($shippingMethod);
            }

            $shipComplete = ($orderAttributesData['partial_ship_order_flag']=='Yes') ? 1 : 0;
            $comments = '';
            $carrierAccountNumber = $this->getCarrierAccountNumber($order->getAllVisibleItems(), $order->getStoreId());
            if ($order->getStoreId() == AccountHelper::ABT_STORE_ID ||
                $order->getStoreId() == $this->accountHelper->getNewSimilacStoreId()) {
                $comments = $orderAttributesData['packing_instruction'];
            } elseif ($order->getStoreId() == AccountHelper::SIM_STORE_ID) {
                $comments = $orderAttributesData['similac_shipping_ins'];
            }
            $orderClassification = $order->getOrderClassification();
            if (!$orderClassification) {
                $currentRule = $this->objectManager->get(
                    RushAttributeSuccess::class
                )->getCurrentOrderClassfication($order->getId());
                $orderClassification = $currentRule->getOrderClassification();
            }


            $commentsData = is_array($comments) ? (empty($comments) ? "" : $comments[0]) : $comments;
            $comment = empty($commentsData) ? $commentsData : htmlspecialchars($commentsData);
            $xmlStr .= '<Order VendorOrderID="'.$incrementId.'" Vendor="'
                .$this->transportHelper->getAccountCode().'" ShippingMethod="'
                .$shippingMethodCode.'" ShipComplete="'
                .$shipComplete.'" CarrierAccountNumber="'.$carrierAccountNumber.'" ClientSubtotalPrice="'
                .$subtotal.'" ClientShippingPrice="'.$shippingAmount.'" ClientTaxAmount="'
                .$taxAmount.'" ClientTotalPrice="'.$totalPrice.'" Comments="'.$comment.'" OrderClassification="'
                .$orderClassification.'">
                    <User UserName="'.$this->transportHelper->getPlaceOrderDefaultUserName().'" Zip="'
                .$this->transportHelper->getPlaceOrderConfig("default_zip").'" State="'
                .$this->transportHelper->getPlaceOrderConfig("default_state").'" Phone="'
                .$this->transportHelper->getPlaceOrderConfig("default_phone").'" LastName="'
                .$this->transportHelper->getPlaceOrderConfig("default_lastname").'" FirstName="'
                .$this->transportHelper->getPlaceOrderConfig("default_firstname").'" Email="'
                .$this->transportHelper->getPlaceOrderConfig("default_email").'" Country="'
                .$this->transportHelper->getPlaceOrderConfig("default_country").'" Company="'
                .$this->transportHelper->getPlaceOrderConfig("default_company").'" City="'
                .$this->transportHelper->getPlaceOrderConfig("default_city").'" Address1="'
                .$this->transportHelper->getPlaceOrderConfig("default_address1").'" Address2="'
                .$this->transportHelper->getPlaceOrderConfig("default_address2").'" CostCenter="'
                .$costCenter.'" />
                    <Recipient FirstName="'.$recipient_firstName.'" LastName="'
                .$recipient_lastName.'" Company="'.$recipientCompany.'" Phone="'
                .$recipientPhone.'" Address1="'.$recipientAddress1.'" Address2="'
                .$recipientAddress2.'" City="'.$recipient_city.'" State="'.$recipientRegion.'" Zip="'
                .$recipient_zip.'" Country="'.$recipientCountry.'" />';
            $xmlStr .= '<OrderItems>';

            foreach ($order->getAllVisibleItems() as $item) {
                $itemId = $item->getId();
                $productCode = $item->getSku();
                $qty = (int)$item->getQtyOrdered();
                $unitPrice = $item->getPrice();
                $netPrice = $item->getRowTotal();
                $dicAmt = $item->getDiscountAmount();
                $xmlStr .= '<OrderItem VendorOrderItemID="'.$itemId.'" ProductCode="'
                    .$productCode.'" Qty="'.$qty.'" ClientUnitPrice="'.
                    $unitPrice.'" ClientNetPrice="'.$netPrice.'" ClientDiscountAmount="'.$dicAmt.'" />';
                if ($order->getStoreId() == AccountHelper::SIM_STORE_ID) {
                    $xmlStr .= '<OrderItem VendorOrderItemID="'.$incrementId.'-'.$itemId.'" ProductCode="'
                        .$this->getPromoProductDetails($order->getCustomerEmail())
                        .'" Qty="1" ClientUnitPrice="0" ClientNetPrice="0" ClientDiscountAmount="0" />';
                }
            }

            $literatureSKU = $this->getLiteratureSKU($order);
            if ($literatureSKU) {
                $xmlStr .= '<OrderItem VendorOrderItemID="'.$order->getId().'" ProductCode="'
                    .$literatureSKU.'" Qty="'.'1'.'" ClientUnitPrice="'.'0'.'" ClientNetPrice="'.'0'
                    .'" ClientDiscountAmount="'.'0'.'" />';
            }

            return $xmlStr.'</OrderItems></Order>';
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
            $this->transportHelper->sendNewRelicAlert(
                new \Exception($ex->getMessage()),
                Transport::PLACEORDER_IDENTIFIER,
                'false'
            );
            return '';
        }
    }

    /**
     * @param $rule_id
     */
    private function getLiteratureSKU($order)
    {

        try {
            $tnSampleConfig = $this->scopeConfig->getValue(
                self::TN_SAMPLE_CONFIG,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $order->getStoreId()
            );
            if ($tnSampleConfig && $order->getCouponCode()) {
                $ruleId = $this->coupon->loadByCode($order->getCouponCode())->getRuleId();
                $rule = $this->ruleRepository->load($ruleId);

                if ($rule->getSamplingStatus() && $rule->getIsActive()) {
                    return $order->getCouponCode();
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->transportHelper->sendNewRelicAlert(
                new \Exception($e->getMessage()),
                Transport::PLACEORDER_IDENTIFIER,
                'false'
            );
        }
        return false;
    }

    private function insertPlaceOrderlog($order, $orderId, $orderStatus, $inboundFeedId, $message = null)
    {
        $websiteId = $this->getOrderStoreId($orderId);
        switch ($websiteId) {
            case 1:
                $this->abbottStore ++;
                if ($orderStatus == Transport::STATUS_SUCCESS) {
                    $this->abbottSuccessCounter ++;
                }
                break;
            case 2:
                $this->glucernaStore ++;
                if ($orderStatus == Transport::STATUS_SUCCESS) {
                    $this->glucernaSuccessCounter ++;
                }
                break;
            default:
                $this->similacStore ++;
                if ($orderStatus == Transport::STATUS_SUCCESS) {
                    $this->similacSuccessCounter ++;
                }
        }
        $hhPlaceOrderEntity = $this->hhPlaceOrder->create();
        $hhPlaceOrderEntity->setOrderId($orderId);
        $hhPlaceOrderEntity->setHhData($this->jsonSerializer->serialize($order));
        $hhPlaceOrderEntity->setStatus($orderStatus);
        $hhPlaceOrderEntity->setMessage($message);
        $hhPlaceOrderEntity->setWebsite($this->getOrderStoreId($orderId));
        $hhPlaceOrderEntity->setHhReqId($inboundFeedId);
        $hhPlaceOrderEntity->save();
    }

    private function updateOrderStatus($ordersArray)
    {
        foreach ($ordersArray as $order) {
            $vendorOrderId = $order->getIncrementId();
            $this->changeOrderStatus($vendorOrderId, Transport::ORDER_STATUS_FAIL, '');
        }
    }

    private function updateHhServiceResponse($message, $inboundFeed)
    {
        $inboundFeed->updateReport($inboundFeed->getFeedId(), Transport::STATUS_FAILED, $message);
        $this->getSendEmail(
            Transport::FAILURE_EMAIL_TEMPLATE,
            $inboundFeed->getFileName(),
            $inboundFeed->getCreatedAt()
        );
    }

    private function getAddressDetails($address)
    {
        return [htmlspecialchars($address['firstname']), htmlspecialchars($address['lastname']), $address['email'],
            $address['postcode'], htmlspecialchars($address['street']),
            htmlspecialchars($address['city']), $address['country_id'], $address['telephone']];
    }

    public function getRegionCode($region)
    {
        $regionCode = $this->regionFactory->create();
        $regionCode->getResource()->load($regionCode, $region);
        return $regionCode['code'];
    }

    private function getSendEmail($template, $fileName, $createdAt, $total = null)
    {
        if ($this->transportHelper->isEnabled()) {
            $mails = $this->transportHelper->getToMails();
            $this->transportHelper->sendEmail(
                Transport::PLACEORDER_TEMPLATE,
                $this->emailTemplateData($template, $fileName, $createdAt, $total),
                $mails
            );
        }
    }

    private function emailTemplateData($template, $fileName, $createdAt, $total)
    {
        if ($template == Transport::FAILURE_EMAIL_TEMPLATE) {
            return ['status_fail' => Transport::STATUS_FAILED,'creation_time' => $createdAt];
        }
        $abbottFailureCounter = $this->abbottStore - $this->abbottSuccessCounter;
        $glucernaFailureCounter = $this->glucernaStore - $this->glucernaSuccessCounter;
        $similacFailureCounter = $this->similacStore -$this->similacSuccessCounter;
        return ['file_name' => $fileName,
        'creation_time' => $createdAt,
        'total_orders' => $total,
        'abbott_success' => $this->abbottSuccessCounter,
        'abbott_failure' => $abbottFailureCounter,
        'abbott_total' => $this->abbottStore,
        'glucerna_success' => $this->glucernaSuccessCounter,
        'glucerna_failure' => $glucernaFailureCounter,
        'glucerna_total' => $this->glucernaStore,
        'similac_success' => $this->similacSuccessCounter,
        'similac_failure' => $similacFailureCounter,
        'similac_total' => $this->similacStore,
        ];
    }

    private function changeOrderStatus($orderId, $status, $message)
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(self::INCREMENT_ID, ['eq' => $orderId]);
        foreach ($orderCollection as $order) {
            $updateMessage = '';
            $updateStatus = '';
            if ($status == Transport::ORDER_STATUS_FAIL) {
                $data = $this->getRetryStatus($order->getRetryCount());
                $updateMessage = ($order->getRetryCount()) ? $data[1] : $message;
                $updateStatus = ($order->getRetryCount() >= 3) ? Transport::ORDER_ERROR : self::STATUS_PROCESSING;
                $order->setRetryCount($data[0]++);
            } else {
                $updateMessage = $message;
                $updateStatus = $status;
            }
            $order->setState(self::STATUS_PROCESSING)->setStatus($updateStatus);
            $order->addStatusHistoryComment($updateMessage, $updateStatus);
            $order->save();
        }
    }

    private function getRetryStatus($value)
    {
        switch ($value) {
            case 1:
                return [2,self::STATUS_RETRY1];
            case 2:
                return [3,self::STATUS_RETRY2];
            case 3:
                return [4,self::STATUS_RETRY3];
            default:
                return [1,''];
        }
    }

    private function getOrderStoreId($orderId)
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(self::INCREMENT_ID, ['eq' => $orderId]);
        foreach ($orderCollection as $order) {
            return $order->getStoreId();
        }
    }

    public function deleteHhLogData()
    {
        $daysIndex = $this->transportHelper->getDays();
        try {
            $connection = $this->resource->getConnection();
            $connection->delete(
                Transport::HARTHANK_PLACEORDER_TABLE,
                "created_at < date_sub(CURDATE(),
                INTERVAL " .$daysIndex."  Day)"
            );
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
            $this->transportHelper->sendNewRelicAlert(
                new \Exception($ex->getMessage()),
                Transport::PLACEORDER_IDENTIFIER,
                'false'
            );
        }
    }

    protected function getPromoProductDetails($mail)
    {
        $profileCollection = $this->profileCollectionFactory->create();
        $profileCollection->addFieldToSelect('status')->getSelect()->joinLeft(
            ['manage_monthly_sub' => $this->resource->getTableName('manage_monthly_subscription')],
            'main_table.profile_id = manage_monthly_sub.profile_id',
            ['manage_monthly_sub.current_month','manage_monthly_sub.customer_email']
        )->joinLeft(
            ['manage_disc_rules'=> $this->resource->getTableName('manage_discount_rules')],
            'manage_monthly_sub.current_month = manage_disc_rules.months',
            ['manage_disc_rules.promotional_sku']
        );
        $profileCollection->addFieldToFilter('main_table.customer_email', ['eq' => $mail])
        ->addFieldToFilter('main_table.store_id', ['eq' => '3'])
        ->addFieldToFilter('main_table.status', ['eq' => 'active']);
        return $profileCollection->getFirstItem()->getPromotionalSku();
    }

    public function getOrderAttributesData($order)
    {
        $orderAttributesData = [];
        $entity = $this->entityResolver->getEntityByOrder($order);
        $form = $this->createEntityForm($entity);
        $outputData = $form->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML);
        foreach ($outputData as $attributeCode => $data) {
            if ($attributeCode == 'packingslip_amt' || $attributeCode == 'partial_ship_order_flag') {
                $orderAttributesData[$attributeCode] = ($data != null) ? $data->getText() : $data ;
            } else {
                $orderAttributesData[$attributeCode] = $data;
            }
        }
        return $orderAttributesData;
    }

    protected function createEntityForm($entity)
    {
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('adminhtml_order_view')
          ->setEntity($entity);
        return $formProcessor;
    }

    private function getShippingCode($method)
    {
        switch ($method) {
            case "fedex_FEDEX_GROUND":
                return "FEDEX_GRD";
            break;
            case "fedex_FEDEX_2_DAY":
                return "FEDEX_ECON";
            break;
            case "fedex_STANDARD_OVERNIGHT":
                return "FEDEX_STD";
            break;
            case "fedex_SMART_POST":
                return "FEDEX_PS";
            break;
            case "FEDEX GROUND RESIDENTIAL WITH SIGNATURE":
                return "FEDEX_GRDRS";
            break;
            case "FEDEX 2DAY WITH SIGNATURE":
                return "FEDEX_ECORS";
            break;
            case "FEDEX STANDARD OVERNIGHT WITH SIGNATURE":
                return "FEDEX_STDRS";
            break;
            default:
                return "FEDEX_GRD";
        }
    }

    private function getCustomerCostCenter($id)
    {
        try {
            $customerRepo = $this->customerRepository->getById($id);
            $sportsTeamId = $customerRepo->getCustomAttribute('sports_team_id');
            return empty($sportsTeamId) ? '' : $sportsTeamId->getValue();
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
            $this->transportHelper->sendNewRelicAlert(
                new \Exception($ex->getMessage()),
                Transport::PLACEORDER_IDENTIFIER,
                'false'
            );
            return '';
        }
    }

    private function getCarrierAccountNumber($items, $storeId)
    {
        foreach ($items as $item) {
            try {
                $product = $this->productRepository->getById($item->getProductId());
                $categoryIds = $product->getCategoryIds();
                asort($categoryIds);
                $categoryId = ($storeId == AccountHelper::ABT_STORE_ID) ?
                    $categoryIds[0] : ($this->storeManagerInterface->getStore($storeId)->getRootCategoryId());
                $category = $this->categoryRepository->get($categoryId);
                return $category['brand_carrier_number'];
            } catch (\Exception $ex) {
                $this->logger->critical($ex->getMessage());
                $this->transportHelper->sendNewRelicAlert(
                    new \Exception($ex->getMessage()),
                    Transport::PLACEORDER_IDENTIFIER,
                    'false'
                );
            }
        }
        return '';
    }

    /**
     * Verify Order Void Transaction
     *
     * @param int $orderId
     * @return bool
     */
    protected function verifyOrderTxn(int $orderId): bool
    {
        try {
            $connection = $this->resource->getConnection();
            $select = $connection->select()->reset(Select::COLUMNS)
                ->from(
                    ['main_table' => $this->resource->getTableName(self::SALES_PAYMENT_TRANSACTION_TABLE_NAME)],
                    ['order_id']
                )
                ->where('txn_type = ?', self::STATUS_VOID)
                ->where('is_closed = ?', true)
                ->where('order_id = ?', $orderId);

            $this->hhLogger->debug('In verifyOrderTxn function', '');
            $this->hhLogger->debug('verifyOrderTxn SQL :', $select->__toString());
            $records = $connection->fetchAll($select);
            if (count($records)) {
                $this->hhLogger->debug('HarteHanks Skipping Order ID :', [$orderId]);
                return true;
            }
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }

        return false;
    }

    /**
     * Insert Order IDs into table before sending to HarteHanks
     *
     * @param array $lockOrders
     * @return void
     */
    private function lockOrders(array $lockOrders): void
    {
        try {
            $connection = $this->resource->getConnection();
            $connection->insertMultiple(HhProcessingOrder::TBL_NAME, $lockOrders);
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }
    }

    /**
     * Delete Order IDs from table after sync complete from HarteHanks
     *
     * @param array $lockOrders
     * @return void
     */
    private function deleteLockOrders(array $lockOrders): void
    {
        try {
            $this->hhLogger->debug('In deleteLockOrders function, Order Ids: ', $lockOrders);
            $connection = $this->resource->getConnection();
            $connection->delete(HhProcessingOrder::TBL_NAME, ['order_increment_id IN (?)' => $lockOrders]);
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }
    }

    /**
     * Send Orders To HH
     *
     * @param string $xmlPostString
     * @param array|null $orderIds
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     * @return array|string|void
     * @throws LocalizedException
     */
    public function sendOrdersToHH(string $xmlPostString, array $orderIds = null, $orderCollection = null)
    {
        $response = $this->transportHelper->getCurlResponse($xmlPostString);

        $data = [Transport::FILE_CONTENT_TYPE, Transport::ORDER_FILE_NAME,
            Transport::STATUS_PENDING, Transport::MESSAGE_PENDING];
        $inboundFeed = $this->inboundFeedFactory->create()->submitReport($data);

        if (str_contains($response, 'Service Temporarily Unavailable')) {
            $this->updateHhServiceResponse($response, $inboundFeed);
            $this->transportHelper->sendNewRelicAlert(
                new \Exception($response),
                Transport::PLACEORDER_IDENTIFIER,
                'false'
            );
            return;
        }
        $result = $this->parser->loadXML($response)->xmlToArray();

        if (empty($orderCollection)) {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);
        }

        if (array_key_exists(Transport::SOAP_FAULT, $result[Transport::SOAP_ENVELOPE][Transport::SOAP_BODY])) {
            $this->updateOrderStatus($orderCollection);
            $message = $result[Transport::SOAP_ENVELOPE][Transport::SOAP_BODY][Transport::SOAP_FAULT]['faultstring'];
            $this->updateHhServiceResponse($message, $inboundFeed);
            $this->transportHelper->sendNewRelicAlert(
                new \Exception($message),
                Transport::PLACEORDER_IDENTIFIER,
                'false'
            );
            return;
        }

        $xmlArrayResult = $this->parser->loadXML($result[Transport::SOAP_ENVELOPE][Transport::SOAP_BODY]
        ['ns2:callXMLServiceResponse']['return'])->xmlToArray();

        if (array_key_exists(Transport::SOAP_EXCEPTION, $xmlArrayResult) ||
            array_key_exists(
                Transport::SOAP_EXCEPTION,
                $xmlArrayResult[Transport::SOAP_PO_SERVICE][Transport::SOAP_VALUE]
            )
        ) {
            $this->updateOrderStatus($orderCollection);
            $message = array_key_exists(
                Transport::SOAP_EXCEPTION,
                $xmlArrayResult
            ) ?
                $xmlArrayResult[Transport::SOAP_EXCEPTION][Transport::SOAP_VALUE] :
                $xmlArrayResult[Transport::SOAP_PO_SERVICE]
                [Transport::SOAP_VALUE][Transport::SOAP_EXCEPTION][Transport::SOAP_VALUE];
            $this->updateHhServiceResponse($message, $inboundFeed);

            $orderId = [];
            foreach ($orderCollection as $order) {
                $orderId[] = $order->getIncrementId();
            }
            $this->transportHelper->sendNewRelicAlert(
                new \Exception($message),
                Transport::PLACEORDER_IDENTIFIER,
                implode(',', $orderId)
            );
        }

        $inboundFeedId = $inboundFeed->getFeedId();
        if (array_key_exists(
            Transport::SOAP_ERRORS,
            $xmlArrayResult[Transport::SOAP_PO_SERVICE][Transport::SOAP_VALUE]
        )) {
            $errors = $xmlArrayResult[Transport::SOAP_PO_SERVICE]
            [Transport::SOAP_VALUE][Transport::SOAP_ERRORS]['Error'];

            $errorsArray = [];
            $deleteLockErrorOrders = [];
            if (array_key_exists('0', $errors)) {
                $errorsArray = $errors;
            } else {
                $errorsArray[0] = $errors;
            }
            $this->failureCounter = count($errorsArray);

            foreach ($errorsArray as $error) {
                $orderStatus = Transport::ORDER_ERROR;
                $vendorOrderId = '';

                if (array_key_exists(Transport::SOAP_VENDOR_ORDER_ID, $error[Transport::SOAP_ATTRIBUTE])) {
                    $vendorOrderId = $error[Transport::SOAP_ATTRIBUTE][Transport::SOAP_VENDOR_ORDER_ID];
                }

                $orderId = $vendorOrderId ? $vendorOrderId : '';
                $setOrderStatus = Transport::ORDER_STATUS_FAIL;
                $message = $error[Transport::SOAP_VALUE];

                if (str_contains($message, 'duplicate orders not allowed') && !empty($orderId)) {

                    $findOrderString = '<Filter VendorOrderId="' . $orderId . '"/>';
                    if (empty($this->hhFindOrderSync->findOrder($findOrderString))) {
                        $this->changeOrderStatus($orderId, $setOrderStatus, '');
                        $deleteLockErrorOrders[] = $orderId;
                        continue;
                    }
                    $this->changeOrderStatus($orderId, Transport::ORDER_STATUS, '');
                } else {
                    $this->changeOrderStatus($orderId, $setOrderStatus, $message);
                    $this->insertPlaceOrderlog($error, $orderId, $orderStatus, $inboundFeedId, $message);
                    $this->transportHelper->sendNewRelicAlert(
                        new \Exception($message),
                        Transport::PLACEORDER_IDENTIFIER,
                        $orderId
                    );
                }
                $deleteLockErrorOrders[] = $orderId;
            }
            if ($this->lockOrdersFlag) {
                $this->deleteLockOrders($deleteLockErrorOrders);
            }
        }
        if (array_key_exists(
            Transport::SOAP_ORDERS,
            $xmlArrayResult[Transport::SOAP_PO_SERVICE][Transport::SOAP_VALUE]
        )
        ) {
            $orders = $xmlArrayResult[Transport::SOAP_PO_SERVICE][Transport::SOAP_VALUE][Transport::SOAP_ORDERS];
            $ordersArray = [];
            if (array_key_exists('0', $orders)) {
                $ordersArray = $orders;
            } else {
                $ordersArray[0] = $orders;
            }
            $this->successCounter = count($ordersArray);
            $deleteLockOrders = [];
            foreach ($ordersArray as $order) {
                $orderStatus = $order[Transport::SOAP_ATTRIBUTE][Transport::STATUS];
                $orderId = $order[Transport::SOAP_VALUE][Transport::SOAP_ORDER]
                [Transport::SOAP_ATTRIBUTE][Transport::SOAP_VENDOR_ORDER_ID];
                $setOrderStatus = ($orderStatus == Transport::STATUS_SUCCESS) ?
                    Transport::ORDER_STATUS : Transport::ORDER_STATUS_FAIL;
                $this->changeOrderStatus($orderId, $setOrderStatus, '');
                $this->insertPlaceOrderlog($order, $orderId, $orderStatus, $inboundFeedId);
                $deleteLockOrders[] = $orderId;
            }
            if ($this->lockOrdersFlag) {
                $this->deleteLockOrders($deleteLockOrders);
            }
        }
        $status = ($xmlArrayResult[Transport::SOAP_PO_SERVICE]
            [Transport::SOAP_ATTRIBUTE][Transport::STATUS] == 'success') ?
            Transport::STATUS_SUCCESS :
            Transport::STATUS_FAILED;
        $inboundMessage = ["Total Orders" => $orderCollection->count(),
            "Success" => $this->successCounter,
            "Failed" => ($orderCollection->count() - $this->successCounter)
        ];
        $inboundFeed->updateReport($inboundFeedId, $status, $this->jsonSerializer->serialize($inboundMessage));
        if ($status != Transport::STATUS_SUCCESS) {
            $template = Transport::PLACEORDER_TEMPLATE;
            $this->getSendEmail(
                $template,
                $inboundFeed->getFileName(),
                $inboundFeed->getCreatedAt(),
                $orderCollection->count()
            );
        }
        $this->hhLogger->debug('HarteHanks : Place Orders Processing Completed.', $inboundMessage);
        return $xmlArrayResult;
    }
}
