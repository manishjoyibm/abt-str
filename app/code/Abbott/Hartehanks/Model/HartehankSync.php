<?php

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Helper\Transport;
use Abbott\WorkdayFeed\Model\InboundFeedFactory;
use Magento\Framework\Xml\Parser;
use Abbott\Hartehanks\Model\HarteHankFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory as InboundFeedCollectionFactory;
use Abbott\Hartehanks\Model\ResourceModel\HarteHank\CollectionFactory as HarteHankCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

class HartehankSync extends \Magento\Framework\Model\AbstractModel
{
    public $_orderCollectionFactory;
    public $hartHankFactory;
    public $succesCounter;
    public $failCounter;
    protected $transportHelper;

    protected $parser;

    protected $inboundFeedFactory;

    protected $stockRegistry;

    protected $product;

    protected $harteHankCollectionFactory;

    protected $inboundFeedCollection;

    protected $jsonSerializer;

    protected $resource;

    protected $logger;

    protected $directoryList;

    protected $csv;

    protected $file;

    public function __construct(
        Transport $transportHelper,
        InboundFeedFactory $inboundFeedFactory,
        Parser $parser,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        HarteHankFactory $hartHankFactory,
        StockRegistryInterface $stockRegistry,
        Product $product,
        HarteHankCollectionFactory $harteHankCollectionFactory,
        InboundFeedCollectionFactory $inboundFeedCollection,
        Json $jsonSerializer,
        ResourceConnection $resource,
        LoggerInterface $logger,
        DirectoryList $directoryList,
        Csv $csv,
        File $file
    ) {
        $this->transportHelper = $transportHelper;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->parser = $parser;
        $this->hartHankFactory = $hartHankFactory;
        $this->stockRegistry = $stockRegistry;
        $this->product = $product;
        $this->harteHankCollectionFactory = $harteHankCollectionFactory;
        $this->inboundFeedCollection = $inboundFeedCollection;
        $this->jsonSerializer = $jsonSerializer;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->succesCounter = 0;
        $this->failCounter = 0;
        $this->directoryList = $directoryList;
        $this->csv = $csv;
        $this->file = $file;
    }

    public function getPastOrders()
    {
        $orderArray = [];
        $ordershippeddays = $this->transportHelper->getOrderDays();
        $prevDate = date('Y-m-d', strtotime('-'.$ordershippeddays.' days'));
        $currentDate = date('Y-m-d');
        $collection = $this->_orderCollectionFactory->create()->addAttributeToSelect('*');
        $collection->addFieldToFilter(
            'main_table.status',
            ['in' => $this->transportHelper->getOrderStatus()]
        );
        $collection->addAttributeToFilter('main_table.created_at', ['gteq'=>$prevDate.'  00:00:00']);
        $collection->addAttributeToFilter('main_table.created_at', ['lteq'=>$currentDate.'  23:59:59']);
        $collection->getSelect()->join(
            ['order_item' => 'sales_order_item'],
            'main_table.entity_id = order_item.order_id'
        );
        if (!empty($collection->getData())) {
            $skuArray = [];
            foreach ($collection->getData() as $v) {
                $skuArray[$v['sku']][] = $v['qty_ordered'];
            }
            foreach ($skuArray as $sku => $qty) {
                $orderArray[$sku] = array_sum($qty);
            }
        }
        return $orderArray;
    }

    public function execute()
    {
        if ($this->transportHelper->getTestStatus() && $this->transportHelper->getInventoryManagementEnable()) {
            $allOrders = $this->getPastOrders();
            $rootDirectory = $this->directoryList->getPath('media');
            $csvFilePath = 'hh_inventory.csv';
            $csvFile = $rootDirectory . "/test/default/" . $csvFilePath;
            try {
                if ($this->file->isExists($csvFile)) {
                    $this->csv->setDelimiter(",");
                    $data = $this->csv->getData($csvFile);
                    if (!empty($data)) {
                        foreach (array_slice($data, 1) as $value) {
                            $id  = trim($value['0']);
                            $qty = trim($value['1']);
                            if ($this->product->getIdBySku($id) && !empty($allOrders)) {
                                if (isset($allOrders[$id]) && $allOrders[$id] < $qty) {
                                    $qty = $qty - $allOrders[$id];
                                }
                                $this->saveHHLog($id, $qty, 'qqq');
                            }
                        }
                    }
                } else {
                    $this->_logger->info('Csv file not exist');
                    return __('Csv file not exist');
                }
            } catch (FileSystemException $e) {
                $this->_logger->info($e->getMessage());
            }
        } else {
            $data = [Transport::FILE_CONTENT_TYPE, Transport::FILE_NAME,
                Transport::STATUS_PENDING, Transport::MESSAGE_PENDING];
            $inboundFeed = $this->inboundFeedFactory->create();
            $inboundFeed->submitReport($data);

            $xmlPostString = $this->transportHelper->getFindItemQuery();
            try {
                $response = $this->transportHelper->getCurlResponse($xmlPostString);
            } catch (\Exception $ex) {
                $this->logger->critical($ex->getMessage());
            }
            $result = $this->parser->loadXML($response)->xmlToArray();
            $xmlArrayResult = $this->parser->loadXML(
                $result['soap:Envelope']['soap:Body']['ns2:callXMLServiceResponse']['return']
            )->xmlToArray();

            if (array_key_exists(
                Transport::SOAP_EXCEPTION,
                $xmlArrayResult
            ) ||
                array_key_exists(
                    Transport::SOAP_EXCEPTION,
                    $xmlArrayResult[Transport::SOAP_FI_SERVICE][Transport::SOAP_VALUE]
                )
            ) {

                $message = array_key_exists(
                    Transport::SOAP_EXCEPTION,
                    $xmlArrayResult
                ) ?
                    $xmlArrayResult[Transport::SOAP_EXCEPTION][Transport::SOAP_VALUE] :
                    $xmlArrayResult[Transport::SOAP_FI_SERVICE]
                    [Transport::SOAP_VALUE][Transport::SOAP_EXCEPTION][Transport::SOAP_VALUE];
                $this->transportHelper->sendNewRelicAlert(new \Exception($message), 'FindItems', 'false');
                $inboundFeed->updateReport(
                    $this->getInboundId(),
                    Transport::STATUS_FAILED,
                    $xmlArrayResult[Transport::SOAP_EXCEPTION][Transport::SOAP_VALUE]
                );
                $template = Transport::FAILURE_EMAIL_TEMPLATE;
                $this->getSendEmail($template, $inboundFeed->getFileName(), $inboundFeed->getCreatedAt());
                return;

            }
            if ($xmlArrayResult[Transport::SOAP_FI_SERVICE]
                [Transport::SOAP_ATTRIBUTE][Transport::STATUS] == Transport::STATUS_SUCCESS) {
                $items = $xmlArrayResult[Transport::SOAP_FI_SERVICE][Transport::SOAP_VALUE]['Items']['Item'];
                $allOrders = $this->getPastOrders();
                foreach ($items as $item) {
                    $id = $item[Transport::SOAP_ATTRIBUTE]['ItemCode'];
                    $qtyAvailable = $item[Transport::SOAP_ATTRIBUTE]['QtyAvailable'];
                    if ($this->product->getIdBySku($id)) {
                        if ($this->transportHelper->getInventoryManagementEnable()) {
                            if (!empty($allOrders)) {
                                if (isset($allOrders[$id]) && $allOrders[$id] < $qtyAvailable) {
                                    $qtyAvailable = $qtyAvailable - $allOrders[$id];
                                }
                                $this->saveHHLog($id, $qtyAvailable, $item);
                            } else {
                                $this->saveHHLog($id, $qtyAvailable, $item);
                            }
                        } else {
                            $this->saveHHLog($id, $qtyAvailable, $item);
                        }
                    }
                }
                $message = [
                    "Total Records" => count($items),
                    "Success" => $this->succesCounter, "Failed" => $this->failCounter
                ];
                $updateStatus = null;
                if (count($items) == $this->succesCounter) {
                    $updateStatus = Transport::STATUS_SUCCESS;
                } else {
                    $updateStatus = Transport::STATUS_FAILED;
                    $template = Transport::EMAIL_TEMPLATE;
                    $this->getSendEmail(
                        $template,
                        $inboundFeed->getFileName(),
                        $inboundFeed->getCreatedAt(),
                        count($items)
                    );
                }
                $updateMessage = $this->jsonSerializer->serialize($message);
                $inboundFeed->updateReport($this->getInboundId(), $updateStatus, $updateMessage);
            } else {
                $inboundFeed->updateReport(
                    $this->getInboundId(),
                    Transport::STATUS_FAILED,
                    $xmlArrayResult[Transport::SOAP_FI_SERVICE][Transport::SOAP_ATTRIBUTE][Transport::STATUS]
                );
                $template = Transport::FAILURE_EMAIL_TEMPLATE;
                $this->getSendEmail($template, $inboundFeed->getFileName(), $inboundFeed->getCreatedAt());
            }
        }
    }

    private function saveHHLog($id, $qtyAvailable, $item)
    {
        $harthank = $this->hartHankFactory->create();
        $harthank->setProductId($id);
        $harthank->setQtyAvailable($qtyAvailable);
        $harthank->setStatus(Transport::STATUS_PENDING);
        $harthank->setHhData($this->jsonSerializer->serialize($item));
        $harthank->save();
        $saveStatus = $this->updateInventory($id, $qtyAvailable);
        $harthank->load($this->getHHId($id));
        if ($saveStatus != Transport::STATUS_SUCCESS) {
            $harthank->setMessage($saveStatus);
            $harthank->setStatus(Transport::STATUS_FAILED);
        } else {
            $harthank->setStatus($saveStatus);
        }
        $harthank->save();
    }

    private function updateInventory($id, $qtyAvailable)
    {
        try {
            $stockItem = $this->stockRegistry->getStockItemBySku($id);
            $stockItem->setQty($qtyAvailable);
            $this->stockRegistry->updateStockItemBySku($id, $stockItem);
        } catch (\Exception $ex) {
            $this->failCounter ++;
            return $ex->getMessage();
        }
        $this->succesCounter ++;
        return Transport::STATUS_SUCCESS;
    }

    private function getInboundId()
    {
        $model = $this->inboundFeedCollection->create();
        $model->addFieldToFilter('file_name', ['eq' => 'HH Inventory Service']);
        $model->addFieldToFilter(Transport::STATUS, ['eq' => 'Pending']);
        foreach ($model as $inboundEntity) {
            return $inboundEntity->getFeedId();
        }
    }

    private function getHHId($id)
    {
        $model = $this->harteHankCollectionFactory->create();
        $model->addFieldToFilter('product_id', ['eq' => $id]);
        $model->addFieldToFilter(Transport::STATUS, ['eq' => 'Pending']);
        foreach ($model as $hhEntity) {
            return $hhEntity->getHartehankId();
        }
    }

    private function getSendEmail($template, $fileName, $createdAt, $total = null)
    {
        if ($this->transportHelper->isEnabled()) {
            $mails = $this->transportHelper->getToMails();
            $this->transportHelper->sendEmail(
                Transport::EMAIL_TEMPLATE,
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
        return ['file_name' => $fileName,
        'creation_time' => $createdAt,
        'total_records' => $total,
        'added' => $this->succesCounter,
        'failed' => $this->failCounter,
        ];
    }

    public function deleteHhLogData()
    {
        $daysIndex = $this->transportHelper->getDays();
        try {
            $connection = $this->resource->getConnection();
            $connection->delete(
                Transport::HARTHANK_FEED_TABLE,
                "created_at < date_sub(CURDATE(),INTERVAL " .$daysIndex."  Day)"
            );
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }
    }
}
