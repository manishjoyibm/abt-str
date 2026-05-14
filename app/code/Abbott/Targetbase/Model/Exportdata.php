<?php

namespace Abbott\Targetbase\Model;

use Magento\Setup\Exception;
use Abbott\MyAccount\Helper\Data as AccountHelper;

class Exportdata extends \Magento\Framework\Model\AbstractModel
{
    public $_customerRepositoryInterface;
    public $_groupRepository;
    public $_addressFactory;
    public $_subscriber;
    public $_scopeConfig;
    /**
     * @var \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory
     */
    public $inboundFeedCollection;
    const CUSTOMER_FOLDER = 'targetbase_integration/targetbase/targetbase_folder_customer';
    const CUSTOMER_ARCHIVE_FOLDER = 'targetbase_integration/targetbase/targetbase_archive_folder_customer';
    const CUSTOMER_FILENAME = 'targetbase_integration/targetbase/targetbase_customer_filename';
    const FILE_CONTENT_TYPE = 'Targetbase';
    const FILE_NAME = 'TB Customer Data';
    const STATUS_PENDING = 'Pending';
    const MESSAGE_PENDING = 'No Records Processed';
    const STATUS_SUCCESS = 'Success';
    const STATUS_FAILURE = 'Failure';
    const LAST_CUSTOMER_FILE = 'targetbase_integration/targetbase/targetbase_last_customer_file';
    const RECIPIENT = 'targetbase_integration/targetbase/targetbase_pgp_public_keyname';
    const PUBLICKEY = 'targetbase_integration/targetbase/targetbase_pgp_public_key';
    const ERRORMESSAGE = 'Error message';
    const EXCEPTION = 'exception';
    const ONETIMESYNC = 'targetbase_integration/targetbase/targetbase_onetime';
    const ONETIMESYNCDATE = 'targetbase_integration/targetbase/targetbase_onetime_date';
    const EXPORT_CUSTOMER_SUBJECT = 'Targetbase Customer Weekly Report';
    const EXPORT_CUSTOMER_SUBJECT_FROM_TO = 'Targetbase Customer Report';

    /**
     * @var ResourceModel\Targetbase\CollectionFactory
     */
    protected $targetbaseCollection;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;
    /**
     * @var \Magento\Customer\Model\ResourceModel\GroupRepository
     */
    protected $groupRepository;
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $subscriber;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Abbott\WorkdayFeed\Model\InboundFeedFactory
     */
    protected $inboundFeedFactory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptorInterface;
    /**
     * @var \Magento\Framework\Shell
     */
    protected $shell;
    /**
     * @var BaseTargetbase
     */
    protected $baseTargetbase;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Abbott\MyAccount\Helper\Data
     */
    protected $accountHelper;

    /**
     * Exportdata constructor.
     * @param ResourceModel\Targetbase\CollectionFactory $targetbaseCollection
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Abbott\WorkdayFeed\Model\InboundFeedFactory $inboundFeedFactory
     * @param \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory $inboundFeedCollection
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface
     * @param \Magento\Framework\Shell
     * @param BaseTargetbase
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory,
     */
    public function __construct(
        \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory $targetbaseCollection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Abbott\WorkdayFeed\Model\InboundFeedFactory $inboundFeedFactory,
        \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory $inboundFeedCollection,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Framework\Shell $shell,
        BaseTargetbase $baseTargetbase,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        AccountHelper $accountHelper
    ) {
        $this->targetbaseCollection = $targetbaseCollection;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_groupRepository = $groupRepository;
        $this->_addressFactory = $addressFactory;
        $this->_subscriber= $subscriber;
        $this->_scopeConfig = $scopeConfig;
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->inboundFeedCollection = $inboundFeedCollection;
        $this->jsonSerializer = $jsonSerializer;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->transportBuilder = $transportBuilder;
        $this->dateTime = $dateTime;
        $this->configWriter = $configWriter;
        $this->encryptorInterface = $encryptorInterface;
        $this->shell = $shell;
        $this->baseTargetbase = $baseTargetbase;
        $this->file = $file;
        $this->logger = $logger;
        $this->customerFactory = $customerFactory;
        $this->accountHelper = $accountHelper;
    }

    public function getOneTimeCustomerData($date)
    {
        return $this->targetbaseCollection->create()
            ->addFieldToFilter('created_at', ['gteq' => $date])
            ->addFieldToFilter('order_id', ['eq' => 0])
            ->setOrder('created_at', 'ASC');
    }

    /**
     * Main Function To Export Customer Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function exportCustomerData()
    {
        $data = [self::FILE_CONTENT_TYPE, self::FILE_NAME, self::STATUS_PENDING, self::MESSAGE_PENDING];
        $inboundFeed = $this->inboundFeedFactory->create();
        $inboundFeed->submitReport($data);
        $newSimilacStoreId = $this->accountHelper->getNewSimilacStoreId();
        $pedialyteStoreId = $this->accountHelper->getPedialyteStoreId();
        try {

            $datacollection = null;
            $weekDateMessage = '';

            if ($this->_scopeConfig->getValue(self::ONETIMESYNC)) {
                $datacollection = $this->getOneTimeCustomerData($this->_scopeConfig->getValue(self::ONETIMESYNCDATE));
            } else {
                $datacollection = $this->targetbaseCollection->create()
                    ->addFieldToFilter('status', 'pending')
                    ->addFieldToFilter('order_id', ['eq' => 0]);
            }

            $dataDailyTargetbaseCustomer = null;
            if ($this->_scopeConfig->getValue(self::ONETIMESYNC)) {
                $dataDailyTargetbaseCustomer = $this->getOneTimeCustomerData(
                    $this->_scopeConfig->getValue(self::ONETIMESYNCDATE)
                );
                $weekDateMessage = ' - From '.date(
                    'd M Y',
                    strtotime(
                        $this->_scopeConfig->getValue(
                            self::ONETIMESYNCDATE
                        )
                    )
                );
            } else {
                $dataDailyTargetbaseCustomer = $this->targetbaseCollection->create()
                    ->addFieldToFilter('status', 'pending')
                    ->addFieldToFilter('order_id', ['eq' => 0])
                    ->setOrder('created_at', 'ASC');
                $weekDateMessage = ' - Week Of '.date('d M Y', strtotime("-7 days"));
            }

            $dataDailyTargetbaseCustomer->getSelect()->columns(
                [
                    'CustomerCount' => 'COUNT(entity_id)'
                ]
            )->group('DATE_FORMAT(created_at, "%d-%m-%y")');

            $datacollectionSize = $datacollection->getSize();
            if ($datacollectionSize > 0) {
                $recipientdata = $this->_scopeConfig->getValue(self::RECIPIENT);
                $publickey = $this->_scopeConfig->getValue(self::PUBLICKEY);
                $fileName = $this->getCustomerFileName();
                $filepath = $this->getCustomerFilePath();
                $oldFilePath = $this->getOldCustomerFilePath();
                $filePathaAchive = $this->getCustomerArchiveFilePath();
                $fileexist = $this->checkFileExists();
                if ($fileexist) {
                    $this->baseTargetbase->moveFiles($oldFilePath, $filePathaAchive, self::CUSTOMER_ARCHIVE_FOLDER);
                }
                $fileopen = $this->file->fileOpen($filepath, 'w');
                $abtNew = 0;
                $abtUpdate = 0;
                $gluNew = 0;
                $gluUpdate = 0;
                $simNew = 0;
                $simUpdate = 0;
                $newSimNew = 0;
                $newsimUpdate = 0;
                $pdlNew = 0;
                $pdlUpdate = 0;
                $isCron = 1;

                $dailyCustomerCount = "<table border='1' width='100%' style='text-align:center;'><tr><td><b>Date</b></td><td><b>Customer Count</b></td></tr>";
                $totalCount = 0;
                foreach ($dataDailyTargetbaseCustomer as $col) {
                    $totalCount += $col['CustomerCount'];
                    $dailyCustomerCount .= "<tr>
                    <td>".date('d-M-Y', strtotime($col['created_at']))."</td> <td>".$col['CustomerCount']."</td>
                    </tr>";
                }
                $dailyCustomerCount .= "<tr><td><b>Total Count</b></td><td><b>".$totalCount."</b></td></tr></table>";

                foreach ($datacollection as $data) {
                    $this->writeData($data, $fileopen, $isCron);
                    ($data->getStoreId() == AccountHelper::ABT_STORE_ID &&
                        $data->getIsRegistration() == 1) ? $abtNew++ : null;
                    ($data->getStoreId() == AccountHelper::ABT_STORE_ID &&
                        $data->getIsRegistration() == 0) ? $abtUpdate++ : null;
                    ($data->getStoreId() == AccountHelper::GLU_STORE_ID &&
                        $data->getIsRegistration() == 1) ? $gluNew++ : null;
                    ($data->getStoreId() == AccountHelper::GLU_STORE_ID &&
                        $data->getIsRegistration() == 0) ? $gluUpdate++ : null;
                    ($data->getStoreId() == AccountHelper::SIM_STORE_ID &&
                        $data->getIsRegistration() == 1) ? $simNew++ : null;
                    ($data->getStoreId() == AccountHelper::SIM_STORE_ID &&
                        $data->getIsRegistration() == 0) ? $simUpdate++ : null;
                    ($data->getStoreId() == $newSimilacStoreId && $data->getIsRegistration() ==
                        1) ? $newSimNew++ : null;
                    ($data->getStoreId() == $newSimilacStoreId && $data->getIsRegistration() ==
                        0) ? $newsimUpdate++ : null;
                    ($data->getStoreId() == $pedialyteStoreId && $data->getIsRegistration() ==
                        1) ? $pdlNew++ : null;
                    ($data->getStoreId() == $pedialyteStoreId && $data->getIsRegistration() ==
                        0) ? $pdlUpdate++ : null; 
                    $data->setStatus("success");
                    $data->save();
                }

                $this->file->fileClose($fileopen);

                if (!empty($dailyCustomerCount)) {
                    $this->baseTargetbase->getSendExportEmail(
                        $dailyCustomerCount,
                        self::EXPORT_CUSTOMER_SUBJECT.$weekDateMessage
                    );
                }

                if ($recipientdata && $publickey) {
                    $this->baseTargetbase->encryptFile($filepath, self::RECIPIENT);
                    $this->setLastCustomerFile($fileName);
                    $totalAbt = $abtNew + $abtUpdate;
                    $totalGlu = $gluNew + $gluUpdate;
                    $totalSim = $simNew + $simUpdate;

                    $totalNewSim = $newSimNew + $newsimUpdate;
                    $totalPdl = $pdlNew + $pdlUpdate;
                    $totalAdd = $abtNew + $gluNew + $simNew + $newSimNew + $pdlNew;
                    $totalUpdate = $abtUpdate + $gluUpdate + $simUpdate + $newsimUpdate + $pdlUpdate;

                    $message = [
                        "Total No Records" => $datacollectionSize,
                        "Add" => $totalAdd,
                        "Update" => $totalUpdate,
                        "ABT" => $totalAbt,
                        "ABT Add" => $abtNew,
                        "ABT Update" => $abtUpdate,
                        "GLU" => $totalGlu,
                        "GLU Add" => $gluNew,
                        "GLU Update" => $gluUpdate,
                        "SIM" => $totalSim,
                        "SIM Add" => $simNew,
                        "SIM Update" => $simUpdate,
                        "NEWSIM" => $totalNewSim,
                        "NEWSIM Add" => $newSimNew,
                        "NEWSIM Update" => $newsimUpdate,
                        "PDL" => $totalPdl,
                        "PDL Add" => $pdlNew,
                        "PDL Update" => $pdlUpdate
                    ];

                    $updateMessage = $this->jsonSerializer->serialize($message);
                    $inboundFeed->updateReport(
                        $this->baseTargetbase->getInboundId(self::FILE_NAME),
                        self::STATUS_SUCCESS,
                        $updateMessage
                    );
                } else {
                    $updateMessage="Customer File Encryption is failed with the exception ".
                        "message either public key or username is not available";
                    $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
                    $inboundFeed->updateReport(
                        $this->baseTargetbase->getInboundId(self::FILE_NAME),
                        self::STATUS_FAILURE,
                        $updateMessage
                    );
                    $this->baseTargetbase->getSendEmail($updateMessage);
                    throw new \Magento\Framework\Exception\LocalizedException(__($updateMessage));
                }
            } else {
                $updateMessage = "No Customer Records To Process";
                $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
                $inboundFeed->updateReport(
                    $this->baseTargetbase->getInboundId(self::FILE_NAME),
                    self::STATUS_FAILURE,
                    $updateMessage
                );
                $this->baseTargetbase->getSendEmail($updateMessage);
                throw new \Magento\Framework\Exception\LocalizedException(__($updateMessage));
            }
        } catch (\Exception $e) {
            $updateMessage="Customer File Generation is failed with the exception message " . $e->getMessage();
            $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
            $inboundFeed->updateReport(
                $this->baseTargetbase->getInboundId(self::FILE_NAME),
                self::STATUS_FAILURE,
                $updateMessage
            );
            $this->baseTargetbase->getSendEmail($updateMessage);
        }
    }
    /**
     * For Loading the values to write in file
     *
     * @param mixed $data Customer Data
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCommonValues($data, $isCron = true)
    {
        $customer = $this->_customerRepositoryInterface->getById($data->getCustomerId());
        $customerId = $data->getCustomerId();
        $customerGroupId = $customer->getGroupId();
        $customerGroup = $this->_groupRepository->getById($customerGroupId);
        $shippingAddressId = $customer->getDefaultShipping();
        $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);
        $checkSubscriber = $this->_subscriber->loadByCustomerId($customerId);
        $suscriptionStatus=($checkSubscriber->isSubscribed()) ? 1 : 0;

        if ($checkSubscriber->getChangeStatusAt()) {
            $doNotEmailUpdated = date("Ymd", strtotime($checkSubscriber->getChangeStatusAt()));
        } else {
            $doNotEmailUpdated = $checkSubscriber->getChangeStatusAt();
        }

        if (date(
            "Y-m-d h:i:s",
            strtotime($customer->getCreatedAt())
        ) == date("Y-m-d h:i:s", strtotime($customer->getUpdatedAt()))) {
            $recordtype= "N";
        } else {
            $recordtype= "U";
        }
        $street = $shippingAddress->getStreet();
        $street2 = (count($street) > 1) ? $street[1] : null;
        return [
            $customerId,
            $customerGroup->getCode(),
            "M",
            date("Ymd", strtotime($customer->getCreatedAt())),
            date("Ymd", strtotime($customer->getUpdatedAt())),
            $customer->getFirstname(),
            $customer->getMiddlename(),
            $customer->getLastname(),
            $shippingAddress->getCompany(),
            $customerGroup->getCode(),
            $street[0],
            $street2,
            $shippingAddress->getCity(),
            $shippingAddress->getRegion(),
            $shippingAddress->getPostcode(),
            $shippingAddress->getCountry(),
            $suscriptionStatus,
            $doNotEmailUpdated,
            $customer->getEmail(),
            1,
            $suscriptionStatus,
            $doNotEmailUpdated,
            "",
            $shippingAddress->getTelephone(),
            "",
            $shippingAddress->getTelephone(),
            $recordtype
            ];
    }
    /**
     * To Write the File
     *
     * @param array $data     Customer Data
     * @param file  $fileopen File to write
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function writeData($data, $fileopen, $isCron)
    {
        $common = $this->getCommonValues($data, $isCron);
        fputs($fileopen, implode('|', $common)."\n");
    }
    /**
     * For Getting Customer Filename
     *
     * @return string
     */
    public function getCustomerFileName()
    {
        $systemFilename=$this->_scopeConfig->getValue(self::CUSTOMER_FILENAME);

        return $systemFilename . '_' . $this->dateTime->date('Y-m-d_His') . '.txt';
    }
    /**
     * For Customer File Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCustomerFilePath()
    {
        $varPath = $this->baseTargetbase->getVarPath();
        return $varPath . $this->_scopeConfig->getValue(self::CUSTOMER_FOLDER) . $this->getCustomerFileName();
    }

    /**
     * For Getting old customer file path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getOldCustomerFilePath()
    {
        $varPath = $this->baseTargetbase->getVarPath();
        return $varPath . $this->_scopeConfig->getValue(self::CUSTOMER_FOLDER) . $this->getLastCustomerFile();
    }

    /**
     * For saving last customer filename
     *
     * @param string $fileName Customer File name
     *
     * @return void
     */
    public function setLastCustomerFile($fileName)
    {
        $this->configWriter->save(
            self::LAST_CUSTOMER_FILE,
            $fileName . '.pgp',
            $this->_scopeConfig::SCOPE_TYPE_DEFAULT,
            0
        );
    }

    /**
     * For getting last customer file name
     *
     * @return mixed
     */
    public function getLastCustomerFile()
    {
        return $this->_scopeConfig->getValue(self::LAST_CUSTOMER_FILE);
    }

    /**
     * For Getting Customer Archive File Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCustomerArchiveFilePath()
    {
        $varpath = $this->baseTargetbase->getVarPath();
        return $varpath . $this->_scopeConfig->getValue(self::CUSTOMER_ARCHIVE_FOLDER) .
            $this->_scopeConfig->getValue(self::LAST_CUSTOMER_FILE);
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
        $filepath = $varPath . $this->_scopeConfig->getValue(self::CUSTOMER_FOLDER) . $this->getLastCustomerFile();
        return $this->file->isExists($filepath);
    }

    /**
     * Main Function To Export Customer Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function exportCustomerDataWithDate($fromDate, $toDate)
    {
        $exportDateMessage = ' From '.date('d M Y', strtotime($fromDate)).' - To '.date('d M Y', strtotime($toDate));

        if ($fromDate > $toDate) {
            return "From date should be less than to date";
        }
        $data = [self::FILE_CONTENT_TYPE, self::FILE_NAME, self::STATUS_PENDING, self::MESSAGE_PENDING];
        $inboundFeed = $this->inboundFeedFactory->create();
        $inboundFeed->submitReport($data);
        $fileName = null;
        $newSimilacStoreId = $this->accountHelper->getNewSimilacStoreId();
        $pedialyteStoreId = $this->accountHelper->getPedialyteStoreId();
        try {

            $datacollection = $this->targetbaseCollection->create()
                ->addFieldToFilter('order_id', ['eq' => 0])
                ->addFieldToFilter('created_at', ['gteq' => $fromDate])
                ->addFieldToFilter('created_at', ['lteq' => $toDate])->load();

            $dailyTargebaseCustomer = $this->targetbaseCollection->create()
                ->addFieldToFilter('order_id', ['eq' => 0])
                ->addFieldToFilter('created_at', ['gteq' => $fromDate])
                ->addFieldToFilter('created_at', ['lteq' => $toDate])
                ->setOrder('created_at', 'ASC');

            $dailyTargebaseCustomer->getSelect()->columns(
                ['CustomerCount' => 'COUNT(entity_id)']
            )->group(
                'DATE_FORMAT(created_at, "%d-%m-%y")'
            );

            $datacollectionSize = $datacollection->getSize();
            if ($datacollectionSize > 0) {
                $recipientdata = $this->_scopeConfig->getValue(self::RECIPIENT);
                $publickey = $this->_scopeConfig->getValue(self::PUBLICKEY);
                $fileName = $this->getCustomerFileName();
                $filepath = $this->getCustomerFilePath();
                $oldFilePath = $this->getOldCustomerFilePath();
                $filePathaAchive = $this->getCustomerArchiveFilePath();
                $fileexist = $this->checkFileExists();
                if ($fileexist) {
                    $this->baseTargetbase->moveFiles($oldFilePath, $filePathaAchive, self::CUSTOMER_ARCHIVE_FOLDER);
                }
                $fileopen = $this->file->fileOpen($filepath, 'w');
                $abtNew = 0;
                $gluNew = 0;
                $simNew = 0;
                $newSimNew = 0;
                $isCron = 0;
                $pdlNew = 0;
                foreach ($datacollection as $data) {
                    $this->writeData($data, $fileopen, $isCron);
                    ($data->getStoreId() == AccountHelper::ABT_STORE_ID) ? $abtNew++ : null;
                    ($data->getStoreId() == AccountHelper::GLU_STORE_ID) ? $gluNew++ : null;
                    ($data->getStoreId() == AccountHelper::SIM_STORE_ID) ? $simNew++ : null;
                    ($data->getStoreId() == $newSimilacStoreId) ? $newSimNew++ : null;
                    ($data->getStoreId() == $pedialyteStoreId) ? $pdlNew++ : null;
                }

                $this->file->fileClose($fileopen);

                if ($recipientdata && $publickey) {
                    $this->baseTargetbase->encryptFile($filepath, self::RECIPIENT);
                    $this->setLastCustomerFile($fileName);

                    $message = [
                        "Total No Records" => $datacollectionSize,
                        "ABT Add" => $abtNew,
                        "GLU Add" => $gluNew,
                        "SIM Add" => $simNew,
                        "NEWSIM Add" => $newSimNew,
                        "PDL Add" => $pdlNew
                    ];
                    $updateMessage = $this->jsonSerializer->serialize($message);
                    $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_SUCCESS, $updateMessage);
                } else {
                    $fileName = null;
                    $updateMessage = "Customer File Encryption is failed with the exception ".
                        "message either public key or username is not available";
                    $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
                    $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
                    throw new \Magento\Framework\Exception\LocalizedException(__($updateMessage));
                }

                $dailyCustomerCount = "<table border='1' width='100%' style='text-align:center;'><tr><td><b>Date</b></td><td><b>Customer Count</b></td></tr>";
                $totalCount = 0;
                foreach ($dailyTargebaseCustomer as $col) {
                    $totalCount += $col['CustomerCount'];
                    $dailyCustomerCount .= "<tr>
                    <td>".date('d-M-Y', strtotime($col['created_at']))."</td> <td>".$col['CustomerCount']."</td>
                    </tr>";
                }

                $dailyCustomerCount .= "<tr><td><b>Total Count</b></td><td><b>".$totalCount."</b></td></tr></table>";

                if (!empty($dailyCustomerCount)) {
                    $this->baseTargetbase->getSendExportEmail(
                        $dailyCustomerCount,
                        self::EXPORT_CUSTOMER_SUBJECT.$exportDateMessage
                    );
                }

            } else {
                $fileName = null;
                $updateMessage = "No Customer Records To Process";
                $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
                $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
                throw new \Magento\Framework\Exception\LocalizedException(__($updateMessage));
            }
        } catch (\Exception $e) {
            $fileName = null;
            $updateMessage = "Customer File Generation is failed with the exception message " . $e->getMessage();
            $this->logger->error(self::ERRORMESSAGE, [self::EXCEPTION => $updateMessage]);
            $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
        }

        return ($fileName) ? $fileName : $updateMessage;
    }


    /**
     * For Customer File Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCustomerFilePathConsole($fileName = null)
    {
        $varPath = $this->baseTargetbase->getVarPath();
        return $varPath . $this->_scopeConfig->getValue(self::CUSTOMER_FOLDER) . $fileName;
    }

    /**
     * Check if File exists via console scripts
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function checkCustomerFileExistsConsole($fileName = null)
    {
        $varPath = $this->baseTargetbase->getVarPath();
        $filepath = $varPath . $this->_scopeConfig->getValue(self::CUSTOMER_FOLDER) . $fileName;
        return $this->file->isExists($filepath);
    }
}
