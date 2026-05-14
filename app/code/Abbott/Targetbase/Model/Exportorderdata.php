<?php
namespace Abbott\Targetbase\Model;

use Abbott\MyAccount\Helper\Data as AccountHelper;
use Abbott\OrderManagement\Helper\Data as OrderManagementHelper;
use Abbott\Targetbase\Api\Data\TargetbaseOrderInterface;

class Exportorderdata extends \Magento\Framework\Model\AbstractModel
{
    public $_scopeConfig;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $_productRepository;
    /**
     * @var never[]
     */
    public $orderCounts;
    const ORDER_FOLDER = 'targetbase_integration/targetbase/targetbase_folder_order';
    const ORDER_ARCHIVE_FOLDER = 'targetbase_integration/targetbase/targetbase_archive_folder_order';
    const ORDER_FILENAME = 'targetbase_integration/targetbase/targetbase_order_filename';
    const FILE_CONTENT_TYPE = 'Targetbase';
    const FILE_NAME = 'TB Purchase Data';
    const STATUS_PENDING = 'Pending';
    const MESSAGE_PENDING = 'No Records Processed';
    const STATUS_SUCCESS = 'Success';
    const STATUS_FAILURE = 'Failure';
    const LAST_ORDER_FILE = 'targetbase_integration/targetbase/targetbase_last_order_file';
    const RECIPIENT = 'targetbase_integration/targetbase/targetbase_pgp_public_keyname';
    const PUBLICKEY = 'targetbase_integration/targetbase/targetbase_pgp_public_key';
    const ERRORMESSAGE = 'Error message';
    const EXCEPTION = 'exception';
    const ONETIMESYNC = 'targetbase_integration/targetbase/targetbase_onetime';
    const ONE_TIME_SYNC_DATE = 'targetbase_integration/targetbase/targetbase_onetime_date';
    const ORDER_STATUS_PARTIALLY = 'partially_shipped';
    const ORDER_STATUS_COMPLETED = 'complete';
    const EXPORT_ORDER_SUBJECT = 'Targetbase Order Weekly Report';
    const EXPORT_ORDER_SUBJECT_FROM_TO = 'Targetbase Order Report';

    /**
     * @var ResourceModel\TargetbaseOrder\CollectionFactory
     */
    protected $targetbasecollection;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderinterface;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    /**
     * @var \Abbott\WorkdayFeed\Model\InboundFeedFactory
     */
    protected $inboundFeedFactory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /**
     * @var BaseTargetbase
     */
    protected $baseTargetbase;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Aheadworks\Sarp2\Model\Profile\Order $snsOrder
     */
    protected $_snsOrder;

    /**
     * @var \Abbott\MyAccount\Helper\Data
     */
    protected $orderManagementHelper;

     /**
      * @var Abbott\OrderManagement\Helper\Data
      */
    protected $accountHelper;

    protected $inboundFeed;

    protected $orderItemCounts;

    protected $storeOrderCounts;

    protected $fileName;

    /**
     * Exportorderdata constructor.
     * @param ResourceModel\TargetbaseOrder\CollectionFactory $targetbasecollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Sales\Api\Data\OrderInterface $orderinterface
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Abbott\WorkdayFeed\Model\InboundFeedFactory $inboundFeedFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface
     * @param BaseTargetbase
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Aheadworks\Sarp2\Model\Profile\Order $snsOrder
     */
    public function __construct(
        ResourceModel\TargetbaseOrder\CollectionFactory $targetbasecollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Sales\Api\Data\OrderInterface $orderinterface,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Abbott\WorkdayFeed\Model\InboundFeedFactory $inboundFeedFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        BaseTargetbase $baseTargetbase,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Aheadworks\Sarp2\Model\Profile\Order $snsOrder,
        OrderManagementHelper $orderManagementHelper,
        AccountHelper $accountHelper
    ) {
        $this->targetbasecollection = $targetbasecollection;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->orderinterface = $orderinterface;
        $this->_productRepository = $productRepository;
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->dateTime = $dateTime;
        $this->configWriter = $configWriter;
        $this->baseTargetbase = $baseTargetbase;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->file = $file;
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_snsOrder = $snsOrder;
        $this->orderManagementHelper = $orderManagementHelper;
        $this->accountHelper = $accountHelper;
        $this->orderCounts = [];
        $this->storeOrderCounts = [];
    }

    /**
     * @return \Abbott\WorkdayFeed\Model\InboundFeed
     */
    protected function getInboundFeed()
    {
        if ($this->inboundFeed == null) {
            $this->inboundFeed = $this->inboundFeedFactory->create();
        }
        return $this->inboundFeed;
    }
    /**
     * Main Function To Export Order Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function exportData()
    {
        $data = [self::FILE_CONTENT_TYPE, self::FILE_NAME, self::STATUS_PENDING, self::MESSAGE_PENDING];
        $inboundFeed = $this->getInboundFeed();
        $inboundFeed->submitReport($data);
        try {
            $oneTimeSync = $this->_scopeConfig->getValue(self::ONETIMESYNC);
            $weekDateMessage = '';
            if ($oneTimeSync) {
                $oneTimeSyncDate = $this->_scopeConfig->getValue(self::ONE_TIME_SYNC_DATE);
                $datacollection = $this->targetbasecollection->create()
                    ->addFieldToFilter('created_at', ['gteq' => $oneTimeSyncDate]);
            } else {
                $datacollection = $this->targetbasecollection->create()
                    ->addFieldToFilter('is_complete', 0);
            }

            $dataDailyTargetbaseOrder = null;
            if ($oneTimeSync) {
                $oneTimeSyncDate = $this->_scopeConfig->getValue(self::ONE_TIME_SYNC_DATE);
                $dataDailyTargetbaseOrder = $this->targetbasecollection->create()
                    ->addFieldToFilter('created_at', ['gteq' => $oneTimeSyncDate])
                    ->setOrder('created_at', 'ASC');
                $weekDateMessage = ' - From '.date(
                    'd M Y',
                    strtotime(
                        $this->_scopeConfig->getValue(
                            self::ONE_TIME_SYNC_DATE
                        )
                    )
                );
            } else {
                $dataDailyTargetbaseOrder = $this->targetbasecollection->create()
                    ->addFieldToFilter('is_complete', 0)
                    ->setOrder('created_at', 'ASC');
                $weekDateMessage = ' - Week Of '.date('d M Y', strtotime("-7 days"));
            }

            $dataDailyTargetbaseOrder->getSelect()->columns(
                ['OrderCount' => 'COUNT(entity_id)']
            )->group('DATE_FORMAT(created_at, "%d-%m-%y")');

            $this->archiveLatestFile();

            $dailyOrderCount = "<table border='1' width='100%' style='text-align:center;'><tr><td><b>Date</b></td><td><b>Order Count</b></td></tr>";
            $totalCount = 0;
            foreach ($dataDailyTargetbaseOrder as $col) {
                $totalCount += $col['OrderCount'];
                $dailyOrderCount .= "<tr>
                <td>".date('d-M-Y', strtotime($col['created_at']))."</td> <td>".$col['OrderCount']."</td>
                </tr>";
            }
            $dailyOrderCount .= "<tr><td><b>Total Count</b></td><td><b>".$totalCount."</b></td></tr></table>";

            $this->processData($datacollection, $dailyOrderCount, $weekDateMessage);

        } catch (\Exception $e) {
            $updateMessage = "Order File Generation is failed with the exception message " . $e->getMessage();
            $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
            $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
            $this->baseTargetbase->getSendEmail($updateMessage);
        }
    }

    /**
     * @param $datacollection
     * @param $dailyOrderCount
     * @param $weekDateMessage
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processData($datacollection, $dailyOrderCount, $weekDateMessage)
    {
        if ($datacollection->getSize() > 0) {
            $filepath = $this->getOrderFilePath();
            $fileopen = $this->file->fileOpen($filepath, 'w');
            foreach ($datacollection as $data) {
                $counter =  $this->getOrderItemCount($data->getOrderId());
                $this->writeData($data, $fileopen, $counter);
                $this->countOrderPerStore($data->getStoreId());
                if (!$this->_scopeConfig->getValue(self::ONETIMESYNC)) {
                    $data->setIsComplete(true);
                    $data->save();
                }
            }
            $this->file->fileClose($fileopen);

            if (!empty($dailyOrderCount)) {
                $this->baseTargetbase->getSendExportEmail(
                    $dailyOrderCount,
                    self::EXPORT_ORDER_SUBJECT.$weekDateMessage
                );
            }

            $this->processReport($filepath);
        } else {
            $inboundFeed = $this->getInboundFeed();
            $updateMessage = "No Order Records To Process";
            $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
            $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
            $this->baseTargetbase->getSendEmail($updateMessage);
        }
    }

    /**
     * @param $filepath
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processReport($filepath)
    {
        $recipientdata = $this->_scopeConfig->getValue(self::RECIPIENT);
        $publickey = $this->_scopeConfig->getValue(self::PUBLICKEY);
        $inboundFeed = $this->getInboundFeed();
        $fileName = $this->getOrderFileName();
        if ($recipientdata && $publickey) {
            $this->baseTargetbase->encryptFile($filepath, self::RECIPIENT);
            $this->setLastOrderFile($fileName);
            $message = ["Total No Orders" => array_sum($this->storeOrderCounts)];
            $message = array_merge($message, $this->storeOrderCounts);
            $updateMessage = $this->jsonSerializer->serialize($message);
            $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_SUCCESS, $updateMessage);
        } else {
            $updateMessage="Order File Encryption is failed with the exception message either".
                " public key or username is not available";
            $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
            $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
            $this->baseTargetbase->getSendEmail($updateMessage);
            throw new \Magento\Framework\Exception\LocalizedException(__($updateMessage));
        }
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function archiveLatestFile()
    {
        $oldFilePath = $this->getOldOrderFilePath();
        $filePathaAchive = $this->getOrderArchiveFilePath();
        $fileexist = $this->checkFileExists();
        if ($fileexist) {
            $this->baseTargetbase->moveFiles($oldFilePath, $filePathaAchive, self::ORDER_ARCHIVE_FOLDER);
        }
    }

    /**
     * @param $storeId
     */
    protected function countOrderPerStore($storeId)
    {
        if (!isset($this->storeOrderCounts[$storeId])) {
            $this->storeOrderCounts[$storeId] = 0;
        }
        $this->storeOrderCounts[$storeId]++;
    }

    /**
     * @param int $orderId
     * @return int
     */
    protected function getOrderItemCount($orderId)
    {
        if (!isset($this->storeOrderCounts[$orderId])) {
            $this->storeOrderCounts[$orderId] = 0;
        }
        $this->storeOrderCounts[$orderId]++;
        return $this->storeOrderCounts[$orderId];
    }

    /**
     * For Loading the values to write in file
     *
     * @param TargetbaseOrderInterface $data    Order Data
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCommonValues($data, $counter = 1)
    {
        $orderId = $data->getOrderId();
        $pedialyteStoreId = $this->accountHelper->getPedialyteStoreId();
        if ($data->getStoreId() == AccountHelper::ABT_STORE_ID) {
            $orderId = '0'.$orderId;
            $storename="ABS";
        } elseif ($data->getStoreId() == AccountHelper::GLU_STORE_ID) {
            $storename="GLU";
        } elseif ($data->getStoreId() == $pedialyteStoreId) {
            $storename="PDL";
        } else {
            $storename="SIM";
        }
        /**
         * Targetbase accept only 30 character for payment method field
         */
        $methodTitle = substr($data->getPaymentMethod(), 0, 30);
        return [
            $storename,
            $data->getCustomerId(),
            $data->getOrderType(),
            $orderId,
            date("Ymd", strtotime($data->getCreatedAt())),
            $data->getCouponCode(),
            strtoupper($methodTitle),
            round($data->getTaxAmount(), 2),
            round($data->getShippingAmount(), 2),
            round($data->getGrandTotal(), 2),
            $counter, // counter
            $data->getProductBrand(),
            $data->getProductSku(),
            $data->getProductName(),
            round($data->getProductQtyOrdered()),
            round($data->getProductPrice(), 2),
            round($data->getProductPrice()*$data->getProductQtyOrdered(), 2)
        ];
    }
    /**
     * To Write the File
     *
     * @param array $data     Customer Data
     * @param array $order    Order Data
     * @param array $item     Order Item Data
     * @param file  $fileopen File to write
     * @param int   $counter  Order Counter
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function writeData($data, $fileopen, $counter = 1)
    {
        $common = $this->getCommonValues($data, $counter);
        fputs($fileopen, implode('|', $common)."\n");
    }
    /**
     * For Getting Order Filename
     *
     * @return string
     */
    public function getOrderFileName()
    {
        if (!$this->fileName) {
            $systemFilename = $this->_scopeConfig->getValue(self::ORDER_FILENAME);

            $this->fileName = $systemFilename . '_' . $this->dateTime->date('Y-m-d_His') . '.txt';
        }
        return $this->fileName;
    }
    /**
     * For Order File Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getOrderFilePath()
    {
        $varPath = $this->baseTargetbase->getVarPath();
        return $varPath . $this->_scopeConfig->getValue(self::ORDER_FOLDER) . $this->getOrderFileName();
    }
    /**
     * For Getting old Order file path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getOldOrderFilePath()
    {
        $varPath = $this->baseTargetbase->getVarPath();
        return $varPath . $this->_scopeConfig->getValue(self::ORDER_FOLDER) . $this->getLastOrderFile();
    }
    /**
     * For saving last order filename
     *
     * @param string $fileName order File name
     *
     * @return void
     */
    public function setLastOrderFile($fileName)
    {
        $this->configWriter->save(
            self::LAST_ORDER_FILE,
            $fileName . '.pgp',
            $this->_scopeConfig::SCOPE_TYPE_DEFAULT,
            0
        );
    }
    /**
     * For getting last order file name
     *
     * @return mixed
     */
    public function getLastOrderFile()
    {
        return $this->_scopeConfig->getValue(self::LAST_ORDER_FILE);
    }
    /**
     * For Getting Order Archive File Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getOrderArchiveFilePath()
    {
        $varpath = $this->baseTargetbase->getVarPath();
        return $varpath . $this->_scopeConfig->getValue(
            self::ORDER_ARCHIVE_FOLDER
        ) . $this->_scopeConfig->getValue(
            self::LAST_ORDER_FILE
        );
    }
    /**
     * Check if File exists
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function checkFileExists()
    {
        $varPath = $this->baseTargetbase->getVarPath();
        $filepath = $varPath . $this->_scopeConfig->getValue(self::ORDER_FOLDER) . $this->getLastOrderFile();
        return $this->file->isExists($filepath);
    }

     /**
      * get Targetbase reports
      *
      * @param  $fromDate
      * @param  $toDate
      * @return string
      */
    public function exportOrderDataWithDate($fromDate, $toDate)
    {
        $exportDateMessage = ' From '.date('d M Y', strtotime($fromDate)).' - To '.date('d M Y', strtotime($toDate));

        $datacollection = $this->targetbasecollection->create()
            ->addFieldToFilter('created_at', ['gteq' => $fromDate])
            ->addFieldToFilter('created_at', ['lteq' => $toDate]);

        $dailyTargebaseOrder = $this->targetbasecollection->create()
            ->addFieldToFilter('created_at', ['gteq' => $fromDate])
            ->addFieldToFilter('created_at', ['lteq' => $toDate])
            ->setOrder('created_at', 'ASC');

        $dailyTargebaseOrder->getSelect()->columns(
            ['OrderCount' => 'COUNT(entity_id)']
        )->group('DATE_FORMAT(created_at,
            "%d-%m-%y")');

        $inboudData = [self::FILE_CONTENT_TYPE, self::FILE_NAME, self::STATUS_PENDING, self::MESSAGE_PENDING];
        $inboundFeed = $this->inboundFeedFactory->create();
        $inboundFeed->submitReport($inboudData);
        $newSimilacStoreId = $this->accountHelper->getNewSimilacStoreId();
        $pedialyteStoreId = $this->accountHelper->getPedialyteStoreId();
        try {
            $datacollectionSize = $datacollection->getSize();
            if ($datacollectionSize > 0) {
                $recipientdata = $this->_scopeConfig->getValue(self::RECIPIENT);
                $publickey = $this->_scopeConfig->getValue(self::PUBLICKEY);
                $fileName = $this->getOrderFileName();
                $filepath = $this->getOrderFilePath();
                $oldFilePath = $this->getOldOrderFilePath();
                $filePathaAchive = $this->getOrderArchiveFilePath();
                $fileexist = $this->checkFileExists();
                if ($fileexist) {
                    $this->baseTargetbase->moveFiles($oldFilePath, $filePathaAchive, self::ORDER_ARCHIVE_FOLDER);
                }
                $fileopen = $this->file->fileOpen($filepath, 'w');
                $abt = 0;
                $glu = 0;
                $sim = 0;
                $newSim = 0;
                $pdl = 0;
                foreach ($datacollection as $data) {
                    $counter =  $this->getOrderItemCount($data->getOrderId());
                    $this->writeData($data, $fileopen, $counter);
                    $this->countOrderPerStore($data->getStoreId());

                    ($data->getStoreId() == 1) ? $abt++ : null;
                    ($data->getStoreId() == 2) ? $glu++ : null;
                    ($data->getStoreId() == 3) ? $sim++ : null;
                    ($data->getStoreId() == $newSimilacStoreId) ? $newSim++ : null;
                    ($data->getStoreId() == $pedialyteStoreId) ? $pdl++ : null;
                }
                $this->file->fileClose($fileopen);

                $dailyOrderCount = "<table border='1' width='100%' style='text-align:center;'><tr><td><b>Date</b></td><td style='text-align:center;'><b>Order Count</b></td></tr>";
                $totalCount = 0;
                foreach ($dailyTargebaseOrder as $col) {
                    $totalCount += $col['OrderCount'];
                    $dailyOrderCount .= "<tr>
                    <td>".date('d-M-Y', strtotime($col['created_at']))."</td> <td style='text-align:center;'>".$col['OrderCount']."</td>
                    </tr>";
                }

                $dailyOrderCount .= "<tr><td><b>Total Count</b></td><td style='text-align:center;'><b>".$totalCount."</b></td></tr></table>";

                if (!empty($dailyOrderCount)) {
                    $this->baseTargetbase->getSendExportEmail(
                        $dailyOrderCount,
                        self::EXPORT_ORDER_SUBJECT_FROM_TO.$exportDateMessage
                    );
                }

                if ($recipientdata && $publickey) {
                    $this->baseTargetbase->encryptFile($filepath, self::RECIPIENT);
                    $this->setLastOrderFile($fileName);
                    $message = [
                        "Total No Orders" => $datacollectionSize,
                        "ABT" => $abt,
                        "GLU" => $glu,
                        "SIM" => $sim,
                        "NEWSIM" => $newSim,
                        "PDL" => $pdl
                    ];
                    $updateMessage = $this->jsonSerializer->serialize($message);
                    $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_SUCCESS, $updateMessage);

                } else {
                    $updateMessage = "Order File Encryption is failed with the exception ".
                        "message either public key or username is not available";
                    $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
                    $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
                }
            } else {
                $updateMessage = "No Order Records To Process";
                $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
                $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
            }
        } catch (\Exception $e) {
            $updateMessage = "Order File Generation is failed with the exception message " . $e->getMessage();
            $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
            $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
        }
        return $updateMessage . PHP_EOL;
    }
}
