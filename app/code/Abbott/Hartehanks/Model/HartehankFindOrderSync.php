<?php

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Helper\Transport;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Xml\Parser;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Abbott\Hartehanks\Model\Method\Logger as HhLogger;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\InvoiceOrder;
use Magento\Sales\Model\Order\Shipment\TrackCreationFactory;
use Magento\Sales\Model\ShipOrder;
use Magento\Sales\Model\Order\Invoice\ItemCreationFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\InvoiceOrderInterface;
use Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\CollectionFactory as SkuCollectionFactory;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class HartehankFindOrderSync extends \Magento\Framework\Model\AbstractModel
{
    public $orderManagement;
    public $invoiceOrder;
    public $trackCreation;
    public $shipOrder;
    public $itemCreation;
    public $shipmentItemCreation;
    public $partialInvoiceOrder;
    public $skuCollectionFactory;
    const FROM_TIME = 'fromTime';

    const TO_TIME = 'toTime';

    const INCREMENT_ID = 'increment_id';

    const STATUS = 'status';

    const CREATED_AT = 'created_at';

    const TIME_FORMAT = 'Y-m-d H:i:s';

    const SHIPPED_LABEL = 'shipped';

    private const TOPIC_NAME = 'hh.findorder.sync';

    private const PROCESS_VIA_QUEUE = 'hartehanks/hartehanks_findorder/process_via_queue';

    protected $transportHelper;

    protected $helperData;

    protected $logger;

    protected $orderCollectionFactory;

    protected $parser;

    protected $hhLogger;

    protected $date;

    protected $order;

    protected $invoiceItemCreationInterfaceFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManagerInterface;

    /**
     * @var PublisherInterface
     */
    protected PublisherInterface $publisher;

    /**
     * @var Json
     */
    protected Json $jsonSerializer;

    /**
     * @param Transport $transportHelper
     * @param OrderInterface $order
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Parser $parser
     * @param HhLogger $hhLogger
     * @param DateTime $date
     * @param OrderManagementInterface $orderManagement
     * @param InvoiceOrder $invoiceOrder
     * @param TrackCreationFactory $trackCreation
     * @param ShipOrder $shipOrder
     * @param ItemCreationFactory $itemCreation
     * @param ShipmentItemCreationInterfaceFactory $shipmentItemCreation
     * @param InvoiceOrderInterface $partialInvoiceOrder
     * @param SkuCollectionFactory $skuCollectionFactory
     * @param InvoiceItemCreationInterfaceFactory $invoiceItemCreationInterfaceFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManagerInterface
     * @param PublisherInterface $publisher
     * @param Json $jsonSerializer
     */
    public function __construct(
        Transport $transportHelper,
        OrderInterface $order,
        OrderCollectionFactory $orderCollectionFactory,
        Parser $parser,
        HhLogger $hhLogger,
        DateTime $date,
        OrderManagementInterface $orderManagement,
        InvoiceOrder $invoiceOrder,
        TrackCreationFactory $trackCreation,
        ShipOrder $shipOrder,
        ItemCreationFactory $itemCreation,
        ShipmentItemCreationInterfaceFactory $shipmentItemCreation,
        InvoiceOrderInterface $partialInvoiceOrder,
        SkuCollectionFactory $skuCollectionFactory,
        InvoiceItemCreationInterfaceFactory $invoiceItemCreationInterfaceFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManagerInterface,
        PublisherInterface $publisher,
        Json $jsonSerializer
    ) {
        $this->transportHelper = $transportHelper;
        $this->order = $order;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->parser = $parser;
        $this->hhLogger = $hhLogger;
        $this->date = $date;
        $this->orderManagement = $orderManagement;
        $this->invoiceOrder = $invoiceOrder;
        $this->trackCreation = $trackCreation;
        $this->shipOrder = $shipOrder;
        $this->itemCreation = $itemCreation;
        $this->shipmentItemCreation = $shipmentItemCreation;
        $this->partialInvoiceOrder = $partialInvoiceOrder;
        $this->skuCollectionFactory = $skuCollectionFactory;
        $this->invoiceItemCreationInterfaceFactory = $invoiceItemCreationInterfaceFactory;
        $this->scopeConfig  = $scopeConfig;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->publisher = $publisher;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Find Order Service
     *
     * Process order from Response recieved through FindOrder Service.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return void
     */
    public function execute()
    {
        $cronStatus = $this->transportHelper->isFindOrderCronEnable();
        $this->hhLogger->debug('Cron-Status', $cronStatus);
        if ($cronStatus) {
            $orderCollection = $this->getOrderCollection();
            $data = $orderCollection->getSelect()->__toString();
            $this->hhLogger->debug('Find-Order-Collection', $data);

            $processViaQueue = $this->scopeConfig->getValue(
                self::PROCESS_VIA_QUEUE,
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManagerInterface->getWebsite()->getId()
            );

            if ($processViaQueue && !empty($orderCollection)) {
                $orderIds = [];
                foreach ($orderCollection as $order) {
                    $orderIds[] = $order->getId();
                }
                $rawData = [
                    'orderIds' => $orderIds,
                ];
                $this->hhLogger->debug('HarteHanks : Publishing Find Orders Data to Queue. OrderIds: ', $orderIds);
                $this->publisher->publish(self::TOPIC_NAME, $this->jsonSerializer->serialize($rawData));
            } else {
                $this->hhLogger->debug('HarteHanks : Processing Find Orders without Queue.', []);
                try {
                    $this->findOrderCollection($orderCollection);
                } catch (\Exception $e) {
                    $this->hhLogger->debug('HarteHanks API Failed. ', $e->getMessage());
                }
            }
        }
    }

    /**
     * Custom FindOrder collection
     * @param  array $incrementId
     */
    public function findOrderTest($incrementId)
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToSelect([self::INCREMENT_ID, self::STATUS, self::CREATED_AT, 'updated_at'])
            ->addFieldToFilter(
                self::STATUS,
                [
                    'in' => [Transport::ORDER_STATUS,
                    Transport::ORDER_STATUS_BACKORDERED, Transport::ORDER_STATUS_PARTIALLY_SHIPPED]
                ]
            )
            ->addFieldToFilter(self::INCREMENT_ID, ['in' => $incrementId]);
        $data = $orderCollection->getSelect()->__toString();
        $this->hhLogger->debug('Find-Order-Collection', $data);
        $this->findOrderCollection($orderCollection);
    }

    /**
     * Process Collection to FindOrder Service
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     * @param array|null $orderIds
     */
    public function findOrderCollection($orderCollection = null, $orderIds = null)
    {
        if (empty($orderCollection)) {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);
        }

        $collectionCount = 0;
        $xmlStr = null;
        $setCount = 0;
        $limit = $this->transportHelper->getFindOrderCollectionSize();
        foreach ($orderCollection as $order) {
            if (in_array($order->getStatus(), [Transport::ORDER_STATUS_BACKORDERED,
                    Transport::ORDER_STATUS_PARTIALLY_SHIPPED]) &&
                ($order->getUpdatedAt() > $this->date->date('Y-m-d'))) {
                continue;
            }
            $xmlStr .= '<Filter VendorOrderId="' . $order->getIncrementId() . '"/>';
            $collectionCount++;
            if ($collectionCount == $limit) {
                $this->processFindOrder($xmlStr);
                $xmlStr = null;
                $collectionCount = 0;
                $setCount++;
            }
            if (($setCount == (int)($orderCollection->getSize() / $limit)) &&
                $collectionCount == ($orderCollection->getSize() % $limit)) {
                $this->processFindOrder($xmlStr);
            }
        }
    }

    /**
     * Process FindOrder
     * @param  string $xmlStr
     */
    private function processFindOrder($xmlStr)
    {
        $ordersArray = $this->findOrder($xmlStr);
        if (!empty($ordersArray)) {
            foreach ($ordersArray as $order) {
                try {
                    $this->orderDetail($order);
                } catch (\Exception $e) {
                    $this->hhLogger->debug('Process-Order', $e->getMessage());
                    $this->transportHelper->sendNewRelicAlert(
                        new \Exception('Process-Order '.$e->getMessage()),
                        Transport::FINDORDER_IDENTIFIER,
                        'false'
                    );
                }
            }
        }
    }

    /**
     * getOrderCollection by status and time Frequency
     * @return array OrderCollection
     */
    private function getOrderCollection()
    {
        $range = $this->getTimeRange();
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToSelect([self::INCREMENT_ID, self::STATUS, self::CREATED_AT, 'updated_at']);
        $orderCollection->addFieldToFilter(
            self::STATUS,
            ['in' => [
                Transport::ORDER_STATUS,
                Transport::ORDER_STATUS_BACKORDERED,
                Transport::ORDER_STATUS_PARTIALLY_SHIPPED
            ]
            ]
        );
        $orderCollection->addFieldToFilter(self::CREATED_AT, ['gteq' => $range[self::FROM_TIME]]);
        $orderCollection->addFieldToFilter(self::CREATED_AT, ['lteq' => $range[self::TO_TIME]]);
        return $orderCollection;
    }

    /**
     * getTimeRange Frequency
     * @return array timerange
     */
    private function getTimeRange()
    {
        $fromTime = strtotime(
            "-" . $this->transportHelper->getFindOrderDays() . " days",
            strtotime(date(self::TIME_FORMAT))
        );
        $range[self::FROM_TIME] = $this->date->date(self::TIME_FORMAT, $fromTime);
        $toTime = strtotime(
            "-" . $this->transportHelper->getFindOrderTime() . " minutes",
            strtotime(date(self::TIME_FORMAT))
        );
        $range[self::TO_TIME] = $this->date->date(self::TIME_FORMAT, $toTime);
        return $range;
    }

    /**
     * Process FindOrder Service
     * @param  string $queryStr
     * @return array  $ordersArray
     */
    public function findOrder($queryStr)
    {
        $xmlStr = '<Filters>' . $queryStr . '</Filters>';
        $xmlPostString = $this->transportHelper->getOrderXmlQuery(Transport::FINDORDER_IDENTIFIER, $xmlStr);
        $this->hhLogger->debug('Find-Order-Request', $xmlPostString);
        $response = $this->transportHelper->getCurlResponse($xmlPostString);
        $this->hhLogger->debug('Find-Order-Response', $response);
        $result = $this->parser->loadXML($response)->xmlToArray();
        if (array_key_exists(Transport::SOAP_FAULT, $result[Transport::SOAP_ENVELOPE][Transport::SOAP_BODY])) {
            $this->hhLogger->debug('HH-Invalid-Result', $result);
            return [];
        }
        $xmlArrayResult = $this->parser->loadXML($result[Transport::SOAP_ENVELOPE]
        [Transport::SOAP_BODY]['ns2:callXMLServiceResponse']['return'])->xmlToArray();

        if (array_key_exists(
            Transport::SOAP_EXCEPTION,
            $xmlArrayResult
        ) ||
            array_key_exists(
                Transport::SOAP_EXCEPTION,
                $xmlArrayResult[Transport::SOAP_FO_SERVICE][Transport::SOAP_VALUE]
            )
        ) {
            $message = array_key_exists(
                Transport::SOAP_EXCEPTION,
                $xmlArrayResult
            ) ?
                $xmlArrayResult[Transport::SOAP_EXCEPTION][Transport::SOAP_VALUE] :
                $xmlArrayResult[Transport::SOAP_FO_SERVICE][Transport::SOAP_VALUE
                ][Transport::SOAP_EXCEPTION][Transport::SOAP_VALUE];
                $this->transportHelper->sendNewRelicAlert(
                    new \Exception($message),
                    Transport::FINDORDER_IDENTIFIER,
                    $queryStr
                );
        }

        if (array_key_exists(
            Transport::SOAP_ERRORS,
            $xmlArrayResult[Transport::SOAP_FO_SERVICE][Transport::SOAP_VALUE]
        )) {

            $errors = $xmlArrayResult[Transport::SOAP_FO_SERVICE]
            [Transport::SOAP_VALUE][Transport::SOAP_ERRORS]['Error'];
            $errorsArray = [];
            if (array_key_exists('0', $errors)) {
                $errorsArray = $errors;
            } else {
                $errorsArray[0] = $errors;
            }

            foreach ($errorsArray as $error) {
                $orderId = $queryStr ? $queryStr : '';
                $message = $error[Transport::SOAP_VALUE];
                    $this->transportHelper->sendNewRelicAlert(
                        new \Exception($message),
                        Transport::FINDORDER_IDENTIFIER,
                        $orderId
                    );
            }
        }

        $ordersArray = [];
        if (!empty($xmlArrayResult[Transport::SOAP_FO_SERVICE][Transport::SOAP_VALUE][Transport::SOAP_ORDERS])) {
            $orders = $xmlArrayResult['Find-Order-Service']
            [Transport::SOAP_VALUE][Transport::SOAP_ORDERS][Transport::SOAP_ORDER];
            if (!empty($orders)) {
                if (array_key_exists('0', $orders)) {
                    $ordersArray = $orders;
                } else {
                    $ordersArray[0] = $orders;
                }
                return $ordersArray;
            }
        }

        return $ordersArray;
    }

    /**
     * Process Order
     * @param  array $order
     */
    private function orderDetail($order)
    {
        $orderStatus = $order[Transport::SOAP_ATTRIBUTE]['OrderStatus'];
        $vendorOrderId = $order[Transport::SOAP_ATTRIBUTE][Transport::SOAP_VENDOR_ORDER_ID];
        $data = [$vendorOrderId, $orderStatus];

        switch ($orderStatus) {
            case Transport::ORDER_STATUS_BACKORDERED_LABEL:
                $this->processBackOrder($data);
                break;
            case Transport::ORDER_STATUS_COMPLETE_LABEL:
                $this->processOrder($data);
                $this->hhLogger->debug('Before Process Cancel Items',[]);
                $this->processCancelItems($data);
                $this->hhLogger->debug('After Process Cancel Items',[]);
                break;
            case Transport::ORDER_STATUS_CANCELLED_LABEL:
                $this->cancelOrder($vendorOrderId);
                break;
            default:
                $this->hhLogger->debug('Other-Status', [$vendorOrderId, $data]);
        }
    }

    /**
     * Process order to BackOrder
     * @param  array $data
     */
    private function processBackOrder($data)
    {
        $orderResult = $this->processXmltoArray($data[0]);
        $orderItems = $orderResult['OrderItems']['OrderItem'];
        $itemsArray = $this->getInvoiceItems($data[0], $orderItems, $this->itemCreation);
        $orderEntity = $this->order->loadByIncrementId($data[0]);
        $this->hhLogger->advanceDebug(
            'Count-Order-Items-ProcessBackOrder',
            [$orderEntity->getIncrementId(), count($itemsArray)]
        );
        try {
            if (empty($itemsArray)) {
                if (in_array(
                    $orderEntity->getStatus(),
                    [Transport::ORDER_STATUS_BACKORDERED,
                    Transport::ORDER_STATUS_PARTIALLY_SHIPPED]
                )) {
                    return;
                }
                $orderEntity->setStatus(Transport::ORDER_STATUS_BACKORDERED);
                $orderEntity->addStatusHistoryComment('', Transport::ORDER_STATUS_BACKORDERED);
                $orderEntity->save();
            } else {
                $packageItems = $orderResult['Packages']['Package'];
                $trackArray = $this->getTrackingItems($data[0], $packageItems);
                $shipmentItemArray = $this->getInvoiceItems($data[0], $orderItems, $this->shipmentItemCreation);
                $this->shipOrder->execute(
                    $orderEntity->getId(),
                    $shipmentItemArray,
                    true,
                    false,
                    null,
                    $trackArray,
                    [],
                    null
                );
                $setPartialShipOrderStatus = true;
                $shipmentDetails = $this->getShippedItems($data[0]); // on Magento
                foreach ($shipmentDetails as $key => $value) {
                    $mageShippedQty = $value['shipped'];
                    foreach ($itemsArray as $shipmentItem) {
                        $invoiceQtyHH = $shipmentItem->getQty(); //from HH
                        if (($key == $shipmentItem->getOrderItemId()) && ($invoiceQtyHH > $mageShippedQty)) {
                            $shipmentItem->setQty($mageShippedQty);
                            $setPartialShipOrderStatus = false;
                        }
                    }
                }
                if ($orderEntity->canInvoice()) {
                    try {
                        $this->partialInvoiceOrder->execute($orderEntity->getId(), true, $itemsArray);
                        // ANAPOLLO-3430 - Bug after upgrade to 2.3.7-p1 loses reference connection to related entities
                        // In this case we need to reload order entity to reflect the latest changes from the invoice
                        // I haven't found a better approach to this issue, the bug should
                        // be filed with Magento regarding this
                        $orderEntity = $this->order->loadByIncrementId($data[0]);
                    } catch (\Exception $e) {
                        $orderEntity = $this->order->loadByIncrementId($data[0]);
                        $this->hhLogger->debug(
                            'Process-BackOrder-Invoice',
                            [$orderEntity->getIncrementId(), $e->getMessage()]
                        );
                        $this->transportHelper->sendNewRelicAlert(
                            new \Exception('Process-BackOrder-Invoice: '.$e->getMessage()),
                            Transport::FINDORDER_IDENTIFIER,
                            $orderEntity->getIncrementId()
                        );
                        $orderEntity->setStatus(Transport::ORDER_STATUS_PENDING_INVOICE);
                        $orderEntity->addStatusHistoryComment($e->getMessage(), $orderEntity->getStatus());
                        $orderEntity->save();
                        return false;
                    }
                }
                if ($setPartialShipOrderStatus) {
                    $orderEntity->setStatus(Transport::ORDER_STATUS_PARTIALLY_SHIPPED);
                    $orderEntity->addStatusHistoryComment('', Transport::ORDER_STATUS_PARTIALLY_SHIPPED);
                    $orderEntity->save();
                }
            }
        } catch (\Exception $e) {
            $this->hhLogger->debug('Process-BackOrder', [$orderEntity->getIncrementId(), $e->getMessage()]);
            $this->transportHelper->sendNewRelicAlert(
                new \Exception('Process-BackOrder: '.$e->getMessage()),
                Transport::FINDORDER_IDENTIFIER,
                $orderEntity->getIncrementId()
            );
            $orderEntity->setStatus(Transport::ORDER_ERROR);
            $orderEntity->addStatusHistoryComment($e->getMessage(), $orderEntity->getStatus());
            $orderEntity->save();
        }
    }

    private function processOrder($data)
    {
        $orderResult = $this->processXmltoArray($data[0]);
        $orderItems = $orderResult['OrderItems']['OrderItem'];
        $itemsArray = $this->getInvoiceItems($data[0], $orderItems, $this->itemCreation);
        $orderEntity = $this->order->loadByIncrementId($data[0]);
        if ($orderEntity->getStatus() != Transport::ORDER_STATUS_COMPLETE) {
            $this->hhLogger->advanceDebug(
                'Count-Order-Items-ProcessOrder',
                [$orderEntity->getIncrementId(), count($itemsArray)]
            );
            if (!empty($itemsArray)) {
                try {
                    $packageItems = $orderResult['Packages']['Package'];
                    $trackArray = $this->getTrackingItems($data[0], $packageItems);
                    $shipmentItemArray = $this->getInvoiceItems($data[0], $orderItems, $this->shipmentItemCreation);
                    $this->shipOrder->execute(
                        $orderEntity->getId(),
                        $shipmentItemArray,
                        true,
                        false,
                        null,
                        $trackArray,
                        [],
                        null
                    );
                    $this->hhLogger->debug('Shipment-Items Data', [$shipmentItemArray]);

                } catch (\Exception $exception) {
                    $this->hhLogger->debug('Process-Order', [$orderEntity->getIncrementId(), $exception->getMessage()]);
                    $this->transportHelper->sendNewRelicAlert(
                        new \Exception('Process-Order: '.$exception->getMessage()),
                        Transport::FINDORDER_IDENTIFIER,
                        $orderEntity->getIncrementId()
                    );
                    $orderEntity->setStatus(Transport::ORDER_ERROR);
                    $orderEntity->addStatusHistoryComment($exception->getMessage(), $orderEntity->getStatus());
                    $orderEntity->save();
                }
            }
            $this->hhLogger->debug('Order-Status', [$orderEntity->getIncrementId(), $orderEntity->getStatus()]);
            $this->processInvoiceItems($orderEntity);
        }
    }

    public function processInvoiceItems(\Magento\Sales\Model\Order $order)
    {
        try {
            $this->hhLogger->advanceDebug('Can-Invoice-Order', [$order->getIncrementId(), $order->canInvoice()]);
            $this->hhLogger->debug('Invoice-Order-Status', [$order->getIncrementId(), $order->getStatus()]);
            if ($order->canInvoice()) {
                $items = $order->getAllVisibleItems();
                $itemsToInvoice = [];
                /** @var \Magento\Sales\Model\Order\Item $item */
                foreach ($items as $item) {
                    if ($item->getQtyShipped() > $item->getQtyInvoiced()) {
                        $qtyToInvoice = $item->getQtyShipped() - $item->getQtyInvoiced();
                        /** @var \Magento\Sales\Api\Data\InvoiceItemCreationInterface $invoiceItem */
                        $invoiceItem = $this->invoiceItemCreationInterfaceFactory->create();
                        $invoiceItem->setOrderItemId($item->getId());
                        $invoiceItem->setQty($qtyToInvoice);
                        $itemsToInvoice[] = $invoiceItem;
                    }
                }
                $this->hhLogger->advanceDebug(
                    'Count-Invoice-Items',
                    [$order->getIncrementId(), count($itemsToInvoice)]
                );
                $this->hhLogger->debug('Before IF Invoice-Order-Status', [$order->getIncrementId(), $order->getStatus()]);
                if (!empty($itemsToInvoice)) {
                    $this->partialInvoiceOrder->execute($order->getId(), true, $itemsToInvoice);
                }
                $this->hhLogger->debug('After IF Invoice-Order-Status', [$order->getIncrementId(), $order->getStatus()]);
            } else {
                $order->setStatus(Transport::ORDER_STATUS_PENDING_INVOICE);
                $order->addStatusHistoryComment(__("This order cannot be invoiced."), $order->getStatus());
                $order->save();
            }
        } catch (\Exception $exception) {
            $this->hhLogger->debug('Process-Invoice-Items', [$order->getIncrementId(), $exception->getMessage()]);
            $this->transportHelper->sendNewRelicAlert(
                new \Exception('Process-Invoice-Items: '.$exception->getMessage()),
                Transport::FINDORDER_IDENTIFIER,
                $order->getIncrementId()
            );
            $order->setStatus(Transport::ORDER_STATUS_PENDING_INVOICE);
            $order->addStatusHistoryComment($exception->getMessage(), $order->getStatus());
            $order->save();
        }
        $this->hhLogger->debug('Function End Invoice-Order-Status', [$order->getIncrementId(), $order->getStatus()]);
    }

    public function processCancelItems($data)
    {
        $orderResult = $this->processXmltoArray($data[0]);
        $orderItems = $orderResult['OrderItems']['OrderItem'];
        $this->hhLogger->debug('Get XML Data', [$data[0]]);
        $orderEntity = $this->order->loadByIncrementId($data[0]);
        $this->hhLogger->debug('Get Order-Data', [$orderEntity->getIncrementId(), $orderEntity->getStatus()]);
        $cancelledItems = [];
        if ($orderEntity->getStatus() != Transport::ORDER_STATUS_COMPLETE) {
            $orderItemArray = [];
            if (array_key_exists('0', $orderItems)) {
                $orderItemArray = $orderItems;
            } else {
                $orderItemArray[0] = $orderItems;
            }

            foreach ($orderItemArray as $orderItem) {
                $orderItemDetailArray = [];
                $orderItemDetail = $orderItem[Transport::SOAP_VALUE]['OrderItemDetails']['OrderItemDetail'];
                if (array_key_exists('0', $orderItemDetail)) {
                    $orderItemDetailArray = $orderItemDetail;
                } else {
                    $orderItemDetailArray[0] = $orderItemDetail;
                }

                foreach ($orderItemDetailArray as $itemDetail) {
                    $orderItemDetail = $itemDetail[Transport::SOAP_ATTRIBUTE];
                    if ($orderItemDetail['Disposition'] == 'User Cancelled' ||
                        $orderItemDetail['Disposition'] == 'Cancelled') {
                        $itemId = (int)$orderItem[Transport::SOAP_ATTRIBUTE]['VendorOrderItemId'];
                        if ($itemId) {
                            $qty = (int)$orderItemDetail['Qty'];
                            if (!isset($cancelledItems[$itemId])) {
                                $cancelledItems[$itemId] = 0;
                            }
                            $cancelledItems[$itemId] += $qty;
                        }
                    }
                }
            }
        }
        $this->hhLogger->debug('Cancelled-Items', [$orderEntity->getIncrementId(), $cancelledItems]);
        if (!empty($cancelledItems)) {
            $this->hhLogger->debug('Cancelled-Items', [$orderEntity->getIncrementId(), $cancelledItems]);
            $items = $orderEntity->getItems();
            $totalCanceled = 0;
            $baseTotalCanceled = 0;

            foreach ($items as $item) {
                if (isset($cancelledItems[$item->getId()]) && $cancelledItems[$item->getId()] > 0) {
                    $item->cancel();
                    $this->hhLogger->debug('Cancelled-Items-QTY', [$item->getId(), $item->getQtyCanceled()]);
                    if ($item->getQtyCanceled() != $cancelledItems[$item->getId()]) {
                        $item->setQtyCanceled($cancelledItems[$item->getId()]);

                        $this->hhLogger->debug(
                            'Partial-Items-Cancel-Override',
                            [
                                $orderEntity->getIncrementId(), $item->getId(),
                                $item->getQtyCanceled(), $cancelledItems[$item->getId()]
                            ]
                        );
                    }
                    // Add to the total canceled amount (regular and base currency)
                    $totalCanceled += $item->getRowTotal();
                    $baseTotalCanceled += $item->getBaseRowTotal();
                }
            }
            // Update order totals
            $orderEntity->setTotalCanceled($totalCanceled);
            $orderEntity->setBaseTotalCanceled($baseTotalCanceled);

            $orderEntity->setTotalDue(
                max(0, $orderEntity->getGrandTotal() - $orderEntity->getTotalPaid() - $totalCanceled)
            );
            $orderEntity->setBaseTotalDue(
                max(0, $orderEntity->getBaseGrandTotal() - $orderEntity->getBaseTotalPaid() - $baseTotalCanceled)
            );

            $orderEntity->save();
        }
    }

    /**
     * Generate Shipment for Order
     * @param  string $incrementId
     */
    private function cancelOrder($incrementId)
    {
        $orderEntity = $this->order->loadByIncrementId($incrementId);
        try {
            $this->orderManagement->cancel($orderEntity->getEntityId());
            $this->hhLogger->debug('Order-Cancelled', [$orderEntity->getIncrementId()]);
        } catch (\Exception $e) {

            /**
             * Check exception if of Braintree authorization expired
             * If yes then change order status to cancel
             */
            $message = $this->transportHelper->getBraintreeAuthorizationExpiredMessage();
            if (isset($message) && !empty($message) && strpos($e->getMessage(), $message)
                !== false && $this->changeOrderStatusToCancel($orderEntity, $e->getMessage())) {
                return;
            }
            $this->hhLogger->debug('Cancel-Error', [$orderEntity->getIncrementId(), $e->getMessage()]);
            $this->transportHelper->sendNewRelicAlert(
                new \Exception('Cancel-Error '.$e->getMessage()),
                Transport::FINDORDER_IDENTIFIER,
                $orderEntity->getIncrementId()
            );

            $orderEntity->setStatus(Transport::ORDER_ERROR);
            $orderEntity->addStatusHistoryComment($e->getMessage(), $orderEntity->getStatus());
            $orderEntity->save();
        }
    }

    /**
     * @param $orderEntity
     * @param $message
     */
    private function changeOrderStatusToCancel($orderEntity, $message)
    {
        try {
            if (isset($message) && !empty($message)) {
                $orderEntity->setStatus(Transport::ORDER_STATUS_CANCEL);
                $orderEntity->setState(Transport::ORDER_STATUS_CANCEL);
                $orderEntity->addStatusHistoryComment($message, $orderEntity->getStatus());
                $orderEntity->save();
                return true;
            } else {
                $this->hhLogger->debug(
                    'Cancel-Error',
                    [$orderEntity->getIncrementId(), 'Braintree Authorization expired message is missing in MBO']
                );
                $this->transportHelper->sendNewRelicAlert(
                    new \Exception('Cancel-Error : Braintree Authorization expired message is missing in MBO'),
                    Transport::FINDORDER_IDENTIFIER,
                    $orderEntity->getIncrementId()
                );
                return false;
            }
        } catch (\Exception $e) {
            $this->hhLogger->debug('Cancel-Error', [$orderEntity->getIncrementId(), $e->getMessage()]);
            $this->transportHelper->sendNewRelicAlert(
                new \Exception('Cancel-Error: '.$e->getMessage()),
                Transport::FINDORDER_IDENTIFIER,
                $orderEntity->getIncrementId()
            );
            return false;
        }
    }

    /**
     * To get list of TrackingId's
     * @param  array $trackingNumbers
     * @return array  $trackArray
     */
    private function getTrackingIds($trackingNumbers)
    {
        $trackArray = [];
        foreach ($trackingNumbers as $trackingNumber) {
            $track = $this->trackCreation->create();
            $track->setTrackNumber($trackingNumber);
            $track->setTitle('Federal Express');
            $track->setCarrierCode('fedex');
            $trackArray[] = $track;
        }
        return $trackArray;
    }

    /**
     * Process OrderDetail Service
     * @param  string $vendorOrderId
     * @return array $orderResult
     */
    private function processXmltoArray($vendorOrderId)
    {
        $xmlStr = '<Filters><Filter VendorOrderId="' . $vendorOrderId . '"/></Filters>';
        $xmlPostString = $this->transportHelper->getOrderXmlQuery('OrderDetail', $xmlStr);
        $this->hhLogger->debug('Order-Detail-Request', $xmlPostString);
        $response = $this->transportHelper->getCurlResponse($xmlPostString);
        $this->hhLogger->debug('Order-Detail-Response', $response);
        $result = $this->parser->loadXML($response)->xmlToArray();
        if (array_key_exists(Transport::SOAP_FAULT, $result[Transport::SOAP_ENVELOPE][Transport::SOAP_BODY])) {
            $this->hhLogger->debug('HH-Invalid-Result', $result);
            return '';
        }
        $orderResult = $this->parser->loadXML($result[Transport::SOAP_ENVELOPE]
        [Transport::SOAP_BODY]['ns2:callXMLServiceResponse']['return'])->xmlToArray();

        return $orderResult[Transport::ORDER_DETAIL_SERVICE]
        [Transport::SOAP_VALUE][Transport::SOAP_ORDERS][Transport::SOAP_VALUE]
        [Transport::SOAP_ORDER][Transport::SOAP_VALUE];
    }

    /**
     * Get Invoice|Shipment Items
     * @param  string $vendorOrderId
     * @param  array $orderItems
     * @param object $itemCreationObj
     * @return array $itemsArray
     */
    private function getInvoiceItems($vendorOrderId, $orderItems, $itemCreationObj)
    {
        $orderItemArray = [];
        if (array_key_exists('0', $orderItems)) {
            $orderItemArray = $orderItems;
        } else {
            $orderItemArray[0] = $orderItems;
        }
        $shippedQty = [];
        $itemsArray = [];
        $promotionalSku = [];
        $shippedItems = $this->getShippedItems($vendorOrderId);
        $orderObj = $this->order->loadByIncrementId($vendorOrderId);
        if ($orderObj->getStoreId() == AccountHelper::SIM_STORE_ID) {
            $promotionalSku = $this->getPromotionalSkus();
        }
        foreach ($orderItemArray as $orderItem) {
            if (($orderObj->getStoreId() == AccountHelper::SIM_STORE_ID) &&
                in_array($orderItem[Transport::SOAP_ATTRIBUTE]['ProductCode'], $promotionalSku)) {
                continue;
            }
            $orderItemDetailArray = [];
            $orderItemDetail = $orderItem[Transport::SOAP_VALUE]['OrderItemDetails']['OrderItemDetail'];
            if (array_key_exists('0', $orderItemDetail)) {
                $orderItemDetailArray = $orderItemDetail;
            } else {
                $orderItemDetailArray[0] = $orderItemDetail;
            }
            foreach ($orderItemDetailArray as $itemDetail) {
                $orderItemDetail = $itemDetail[Transport::SOAP_ATTRIBUTE];
                if ($orderItemDetail['Disposition'] == 'Shipped') {
                    $itemId = (int)$orderItem[Transport::SOAP_ATTRIBUTE]['VendorOrderItemId'];
                    if ($itemId) {
                        $qty = (int)$orderItemDetail['Qty'];
                        $shippedQty[$itemId][] = $qty;
                    }
                }
            }
        }
        foreach ($shippedQty as $key => $value) {
            if (!isset($shippedItems[$key])) {
                continue;
            }

            if (array_sum($value) == $shippedItems[$key][self::SHIPPED_LABEL]) {
                continue;
            }
            $unshippedQty = (array_sum($value) < $shippedItems[$key][self::SHIPPED_LABEL])
                ? array_sum($value) : (array_sum($value) - $shippedItems[$key][self::SHIPPED_LABEL]);
            $item = $itemCreationObj->create();
            $item->setOrderItemId($key);
            $item->setQty($unshippedQty);
            $itemsArray[] = $item;
        }

        return $itemsArray;
    }

    /**
     * Get Shipped Items for Order
     * @param  string $incrementId
     * @return array $shippedStatus
     */
    private function getShippedItems($incrementId)
    {
        $shippedStatus = [];
        $orderObj = $this->order->loadByIncrementId($incrementId);
        $itemCollection = $orderObj->getAllVisibleItems();
        foreach ($itemCollection as $item) {
            $itemId = (int)$item->getId();
            $qtyShipped = (int)$item->getQtyShipped();
            $shippedStatus[$itemId][self::SHIPPED_LABEL] = $qtyShipped;
        }
        return $shippedStatus;
    }

    /**
     * Get Tracking Numbers to ship
     * @param  string $incrementId
     * @param  array $packageItems
     * @return array $trackArray
     */
    private function getTrackingItems($incrementId, $packageItems)
    {
        $orderEntity = $this->order->loadByIncrementId($incrementId);
        $trackCollection = $orderEntity->getTracksCollection();
        $orderTracksArray = [];
        foreach ($trackCollection->getItems() as $track) {
            $orderTracksArray[] = $track->getTrackNumber();
        }
        $packageItemArray = [];
        $trackArray = [];
        if (array_key_exists('0', $packageItems)) {
            $packageItemArray = $packageItems;
        } else {
            $packageItemArray[0] = $packageItems;
        }
        foreach ($packageItemArray as $packageItem) {
            $trackingNumber = $packageItem[Transport::SOAP_ATTRIBUTE]['TrackingNumber'];
            $trackArray[] = $trackingNumber;
        }
        return $this->getTrackingIds(array_diff($trackArray, $orderTracksArray));
    }


    /**
     * Get Similac Promotional Skus
     * @return array $skuArray
     */
    private function getPromotionalSkus()
    {
        $skuCollection = $this->skuCollectionFactory->create()->addFieldToSelect('promotional_sku');
        $skuArray = [];
        foreach ($skuCollection as $sku) {
            $skuArray[] = $sku->getPromotionalSku();
        }
        return $skuArray;
    }
}
