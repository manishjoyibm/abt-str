<?php

declare(strict_types=1);

namespace Abbott\Chargeback\Model;

use Abbott\Chargeback\Helper\Data as ChargebackHelper;
use Abbott\Chargeback\Model\ChargebackFactory;
use Abbott\Chargeback\Model\ResourceModel\Chargeback\CollectionFactory;
use Abbott\WorkdayFeed\Model\InboundFeedFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;
use phpseclib3\Net\SFTP;
use Psr\Log\LoggerInterface;

class ChargebackSync
{
    /**
     * @var InboundFeedFactory
     */
    public InboundFeedFactory $inboundFeedFactory;

    /**
     * @var CollectionFactory
     */
    public CollectionFactory $chargebackCollectionFactory;

    /**
     * @var OrderFactory
     */
    public OrderFactory $orderFactory;

    /**
     * @var Json
     */
    public Json $jsonSerializer;

    /**
     * @var ChargebackHelper
     */
    public ChargebackHelper $chargebackHelper;

    /**
     * @var Csv
     */
    public Csv $csvProcessor;

    /**
     * @var ChargebackFactory
     */
    public ChargebackFactory $chargebackFactory;

    /**
     * @var ScopeConfigInterface
     */
    public ScopeConfigInterface $scopeConfig;

    /**
     * @var TransactionCollectionFactory
     */
    public TransactionCollectionFactory $transactionCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    public OrderRepositoryInterface $orderRepository;

    /**
     * @var File
     */
    public File $file;

    /**
     * @var IoFile
     */
    public IoFile $io;

    /**
     * @var DateTime
     */
    public DateTime $date;

    /**
     * @var LoggerInterface
     */
    public LoggerInterface $logger;

    /**
     * @var int
     */
    public int $successCounter;

    /**
     * @var int
     */
    public int $failureCounter;

    public const SUCCESS = 'Success';
    public const FAILED = 'Failed';
    public const PENDING = 'Pending';
    public const FILENAME = 'file_name';
    public const REASONCODE = 'reason_code';
    public const ORDERNUM = 'order_num';
    public const TXNID = 'txn_id';
    public const RT1 = 'RPDE0017D';
    public const REMOTE_TIMEOUT = 30;
    public const CRON_STRING_PATH = 'chargeback_settings/chargeback_cron/cs_enabled';
    public const CODERECD = 'RECD';

    /**
     * @param InboundFeedFactory $inboundFeedFactory
     * @param OrderFactory $orderFactory
     * @param LoggerInterface $logger
     * @param Json $jsonSerializer
     * @param ChargebackHelper $chargebackHelper
     * @param Csv $csvProcessor
     * @param File $file
     * @param IoFile $io
     * @param DateTime $date
     * @param ChargebackFactory $chargebackFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $chargebackCollectionFactory
     * @param TransactionCollectionFactory $transactionCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        InboundFeedFactory           $inboundFeedFactory,
        OrderFactory                 $orderFactory,
        LoggerInterface              $logger,
        Json                         $jsonSerializer,
        ChargebackHelper             $chargebackHelper,
        Csv                          $csvProcessor,
        File                         $file,
        IoFile                       $io,
        DateTime                     $date,
        ChargebackFactory            $chargebackFactory,
        ScopeConfigInterface         $scopeConfig,
        CollectionFactory            $chargebackCollectionFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        OrderRepositoryInterface     $orderRepository
    ) {
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->chargebackCollectionFactory = $chargebackCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->chargebackHelper = $chargebackHelper;
        $this->csvProcessor = $csvProcessor;
        $this->chargebackFactory = $chargebackFactory;
        $this->scopeConfig = $scopeConfig;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->file = $file;
        $this->io = $io;
        $this->date = $date;
        $this->logger = $logger;
        $this->successCounter = 0;
        $this->failureCounter = 0;
    }

    /**
     * Execute function
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if (!$this->scopeConfig->getValue(
            self::CRON_STRING_PATH
        )) {
            return;
        }
        $this->sftpReader();
    }

    /**
     * Get SFTP Connection
     *
     * @return SFTP
     */
    public function getSFTPConnection(): SFTP
    {
        return new SFTP(
            $this->chargebackHelper->getHost(),
            $this->chargebackHelper->getPort(),
            self::REMOTE_TIMEOUT
        );
    }

    /**
     * SFTP Reader
     *
     * @return void
     * @throws LocalizedException
     */
    public function sftpReader(): void
    {
        $sftpPath = $this->chargebackHelper->getSFTPPath();
        try {
            $ssh = $this->getSFTPConnection();
            if (!$ssh->login($this->chargebackHelper->getUserName(), $this->chargebackHelper->getPassword())) {
                throw new LocalizedException(
                    new Phrase(
                        sprintf(
                            "Unable to open SFTP connection as %s@%s",
                            $this->chargebackHelper->getUserName(),
                            $this->chargebackHelper->getHost()
                        )
                    )
                );
            }

            $ssh->chdir($sftpPath);
            $getPath = $ssh->pwd();
            $result = $ssh->nlist($ssh->pwd());
            $this->readZipFile($result, $ssh, $getPath);
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }
    }

    /**
     * Update Status
     *
     * @param int $feedId
     * @return void
     * @throws LocalizedException
     */
    public function updateStatus($feedId): void
    {
        $this->successCounter = 0;
        $this->failureCounter = 0;
        try {
            $inboundFeed = $this->inboundFeedFactory->create();
            $inboundFeed->getResource()->load($inboundFeed, $feedId);
            $this->insertChargeBackLog($inboundFeed);
            $pendingCollection = $this->chargebackCollectionFactory->create()
                ->addFieldToSelect(['chargeback_id', 'order_id', 'status', 'message', 'chargeback_data'])
                ->addFieldToFilter('status', self::PENDING);
            $size = $pendingCollection->getSize();
            if ($size == 0) {
                $inboundFeed->updateReport($feedId, self::FAILED, "File contains empty records");
                return;
            }
            $this->prepareKountDataAndSendMail($pendingCollection);
            $this->processCollection($pendingCollection);
            $inboundMessage = [
                "Total Records" => $size,
                self::SUCCESS => $this->successCounter,
                self::FAILED => $this->failureCounter
            ];
            if ($this->successCounter == $size) {
                $inboundFeed->updateReport($feedId, self::SUCCESS, $this->jsonSerializer->serialize($inboundMessage));
            } else {
                $inboundFeed->updateReport($feedId, self::FAILED, $this->jsonSerializer->serialize($inboundMessage));
                $templateData = $this->getFailedTemplateData($inboundFeed, $size);
                $this->chargebackHelper->sendEmail(
                    $this->chargebackHelper->getEmailTemplate(),
                    $templateData,
                    $inboundFeed->getFileName(),
                    $this->chargebackHelper->getToMails(),
                    $this->chargebackHelper->getSender()
                );
            }
        } catch (\Exception $e) {
            $inboundFeed->updateReport($feedId, self::FAILED, $e->getMessage());
            $templateData = ['status_fail' => self::FAILED, self::FILENAME => $inboundFeed->getFileName()];
            $this->chargebackHelper->sendEmail(
                $this->chargebackHelper->getEmailTemplate(),
                $templateData,
                $inboundFeed->getFileName(),
                $this->chargebackHelper->getToMails(),
                $this->chargebackHelper->getSender()
            );
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Process Collection
     *
     * @param CollectionFactory $pendingCollection
     * @return void
     */
    private function processCollection($pendingCollection): void
    {
        foreach ($pendingCollection as $chargebackRow) {
            try {
                $order = $this->orderFactory->create()->loadByIncrementId($chargebackRow->getOrderId());
                $order->setStatus('chargeback')->save();
                $chargebackRow->setStatus(self::SUCCESS)->save();
                $this->successCounter++;
            } catch (\Exception $e) {
                $this->failureCounter++;
                $chargebackRow->setStatus(self::FAILED)->setMessage($e->getMessage())->save();
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * Prepare Kount Data And Send Mail
     *
     * @param CollectionFactory $pendingCollection
     */
    private function prepareKountDataAndSendMail($pendingCollection): void
    {
        try {
            $kountData = [];
            foreach ($pendingCollection as $chargebackRow) {
                $kountData[] = $this->jsonSerializer->unserialize($chargebackRow->getChargebackData());
            }
            $fileName = 'kount_export_' . $this->date->timestamp() . '.csv';
            $path = $this->chargebackHelper->getChargebackFilePath();
            if (!$this->file->isExists($path)) {
                $this->io->mkdir($path, 0755);
            }
            $path = $path . $fileName;
            $formattedData = $this->formatKountData($kountData);
            $this->csvProcessor->setDelimiter(',')->setEnclosure('"')->saveData($path, $formattedData);
            $this->chargebackHelper->sendEmail(
                $this->chargebackHelper->getKountEmailTemplate(),
                [],
                $fileName,
                $this->chargebackHelper->getKountToMails(),
                $this->chargebackHelper->getKountSender(),
                true
            );
            $this->chargebackHelper->chargebackFiles($fileName, false);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Format Kount Data
     *
     * @param array $kountData
     * @return array
     */
    private function formatKountData($kountData): array
    {
        $result[] = ['Order Num', 'Code'];
        foreach ($kountData as $value) {
            if (array_key_exists(self::ORDERNUM, $value) && array_key_exists(self::REASONCODE, $value)) {
                $result[] = [$value[self::ORDERNUM], $value[self::REASONCODE]];
            }
        }
        return $result;
    }

    /**
     * Get Failed Template Data
     *
     * @param InboundFeedFactory $inboundFeed
     * @param int $totalRecords
     * @return array
     */
    private function getFailedTemplateData($inboundFeed, $totalRecords)
    {
        return [
            self::FILENAME => $inboundFeed->getFileName(),
            'creation_time' => $inboundFeed->getCreatedAt(),
            'total_records' => $totalRecords,
            'success' => $this->successCounter,
            'failed' => $this->failureCounter
        ];
    }

    /**
     * Insert ChargeBack Log
     *
     * @param InboundFeedFactory $model
     * @return void
     * @throws LocalizedException
     */
    private function insertChargeBackLog($model): void
    {
        try {
            $path = $this->chargebackHelper->getChargebackFilePath() . $model->getFileName();
            $csvData = $this->csvProcessor->getData($path);
            $this->createChargebackLog($csvData, $model);
        } catch (\Exception $e) {
            $model->setStatus(self::FAILED)->setMessage($e->getMessage())->save();
            $templateData = ['status_fail' => 'failed', self::FILENAME => $model->getFileName()];
            $this->chargebackHelper->sendEmail(
                $this->chargebackHelper->getEmailTemplate(),
                $templateData,
                $model->getFileName(),
                $this->chargebackHelper->getToMails(),
                $this->chargebackHelper->getSender()
            );
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Read Zip File
     *
     * @param array $result
     * @param SFTP $ssh
     * @param string $getPath
     * @return void
     */
    private function readZipFile($result, $ssh, $getPath): void
    {
        foreach ($result as $filename) {
            if (stripos($filename, '.zip') !== false) {
                try {
                    $ssh->get(
                        $getPath . '/' . $filename,
                        $this->chargebackHelper->getChargebackFilePath() . $filename
                    );
                    $folderName = 'cb_' . $this->date->timestamp();
                    $targetDir = $this->chargebackHelper->getChargebackFilePath() . $folderName;
                    $source = $this->chargebackHelper->getChargebackFilePath() . $filename;
                    $password = $this->chargebackHelper->getZipPassword();
                    $this->chargebackHelper->extractZipFile($source, $targetDir, $password);
                    $this->submitReport($targetDir, $folderName, $ssh, $getPath, $filename);
                } catch (\Exception $ex) {
                    $this->logger->critical("Exception with CB file : " . $filename);
                    $this->logger->critical($ex->getMessage());
                }
            }
        }
    }

    /**
     * Submit Report
     *
     * @param string $targetDir
     * @param string $folderName
     * @param SFTP $ssh
     * @param string $getPath
     * @param string $filename
     * @return void
     * @throws LocalizedException
     * @throws FileSystemException
     */
    private function submitReport($targetDir, $folderName, $ssh, $getPath, $filename): void
    {
        if ($this->file->isExists($targetDir)) {
            $files = $this->file->readDirectory($targetDir);
            foreach ($files as $fileOrg) {
                if ($this->file->isFile($fileOrg)) {
                    $pathInfo = $this->chargebackHelper->getPathInfo($fileOrg);
                    if ($pathInfo['extension'] == 'dfr') {
                        $inboundFeed = $this->inboundFeedFactory->create();
                        $summaryData = [
                            'ChargeBack',
                            $folderName . '/' . $pathInfo['basename'],
                            self::PENDING,
                            'No Records Added Yet'
                        ];
                        $inboundFeed->submitReport($summaryData);
                        $this->updateStatus($inboundFeed->getFeedId());
                        $this->chargebackHelper->chargebackFiles($inboundFeed->getFileName(), true);
                    }
                }
            }
            $ssh->delete($getPath . '/' . $filename);
        } else {
            $this->logger->critical("The zip file is not extracted");
        }
    }

    /**
     * Create Chargeback Log
     *
     * @param array $csvData
     * @param InboundFeedFactory $model
     * @return void
     */
    public function createChargebackLog($csvData, $model): void
    {
        $processed = 0;
        foreach ($csvData as $rowIndex => $rowData) {
            try {
                if ($rowData[0] == self::RT1) {
                    $transId = trim(strtolower($rowData[9]), '"');
                    $orderId = "";
                    $transaction = $this->transactionCollectionFactory->create()
                        ->addFieldToFilter(self::TXNID, $transId)
                        ->getFirstItem();
                    if ($transaction->getTransactionId()) {
                        $order = $this->orderRepository->get($transaction->getOrderId());
                        $orderId = $order->getIncrementId();
                    }
                    if ($orderId) {
                        $this->createChargebackLogDbRecord($transId, $orderId, $rowData);
                        $processed++;
                    } else {
                        $this->logger->critical("Order not found for transaction " . $transId);
                    }
                }
            } catch (\Exception $ex) {
                $this->logger->critical("Record Exception for row " . $rowIndex . " : " . $ex->getMessage());
            }
        }
        $message = ["Total Records" => $processed];
        $model->setStatus('Processed')->setMessage($this->jsonSerializer->serialize($message))->save();
    }

    /**
     * Create ChargebackLog Db Record
     *
     * @param string $transId
     * @param int $orderId
     * @param array $rowData
     * @return void
     */
    private function createChargebackLogDbRecord($transId, $orderId, $rowData): void
    {
        $chargebackData = $this->jsonSerializer->serialize(
            [
                self::TXNID => $transId,
                self::ORDERNUM => $orderId,
                self::REASONCODE => $rowData[11]
            ]
        );

        $chargeback = $this->chargebackFactory->create();
        $chargeback = $chargeback->load($orderId, 'order_id');

        if (!$chargeback->getOrderId()) {
            $chargeback = $this->chargebackFactory->create();
            $chargeback->setOrderId($orderId);
            $chargeback->setChargebackData($chargebackData);
            $chargeback->setStatus(self::PENDING);
            $chargeback->save();
        }
    }
}
