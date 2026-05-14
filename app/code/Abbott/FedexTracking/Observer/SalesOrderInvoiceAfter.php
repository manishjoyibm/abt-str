<?php

namespace Abbott\FedexTracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Abbott\FedexTracking\Helper\Data;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;

class SalesOrderInvoiceAfter implements ObserverInterface
{
    const XML_FEDEX_TRACKING_ENABLE = "fedex_tracking/fedex_tracking_configuration/is_enabled";
    const SHIPMENT_STATUS = "SHIPPED";


    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    protected $shipmentTrackRepositoryInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $helper;

    protected $curl;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        LoggerInterface $logger,
        Data $helper,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ShipmentTrackRepositoryInterface $shipmentTrackRepositoryInterface
    ) {
        $this->logger = $logger;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipmentTrackRepositoryInterface = $shipmentTrackRepositoryInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $order->getPayment()->getAdditionalInformation('captureId');
        $captureId = $order->getPayment()->getAdditionalInformation('captureId');
        try {
            if ($captureId && $this->helper->getConfigValue(self::XML_FEDEX_TRACKING_ENABLE)) {
                $data = array();
                $shipments = $this->getShipmentDataByOrderId($order->getId());
                $shipment = end($shipments);
                if ($shipment) {
                    $shipmetTrackDatas = $this->getShipmentTrackData($shipment->getEntityId());
                }
                if (!empty($shipmetTrackDatas)) {
                    foreach ($shipmetTrackDatas as $track) {
                        $data['tracking_number'] = $track->getTrackNumber();
                        $data['carrier'] = $track->getTitle();
                        $data['status'] = self::SHIPMENT_STATUS;
                        $data['transaction_id'] = $captureId;
                    }
                    $result[] = $data;
                    $trackers['trackers'] = $result;
                    $this->helper->sendTrackingInfo($trackers);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Shipment by Order id
     *
     * @param int $orderId
     * @return ShipmentInterface[]|null |null
     */
    public function getShipmentDataByOrderId(int $orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
        try {
            $shipments = $this->shipmentRepository->getList($searchCriteria);
            $shipmentRecords = $shipments->getItems();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $shipmentRecords = null;
        }
        return $shipmentRecords;
    }


    public function getShipmentTrackData($entityId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('parent_id', $entityId)->create();
        try {
            $shipments = $this->shipmentTrackRepositoryInterface->getList($searchCriteria);
            $shipmentRecords = $shipments->getItems();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $shipmentRecords = null;
        }
        return $shipmentRecords;
    }
}
