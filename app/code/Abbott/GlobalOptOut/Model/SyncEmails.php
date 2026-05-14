<?php

namespace Abbott\GlobalOptOut\Model;

use Abbott\GlobalOptOut\Helper\Data;
use Abbott\WorkdayFeed\Model\InboundFeedFactory;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Phrase;
use phpseclib3\Net\SFTP;
use Psr\Log\LoggerInterface;

class SyncEmails
{
    private const REMOTE_TIMEOUT = 10;

    private $inboundFeedId = null;

    /**
     * @var File
     */
    private File $file;

    /**
     * @var GlobaloptFactory
     */
    private GlobaloptFactory $globaloptFactory;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var DirectoryList
     */
    private DirectoryList $dir;

    /**
     * @var Csv
     */
    private Csv $csvProcessor;

    /**
     * @var InboundFeedFactory
     */
    private InboundFeedFactory $inboundFeedFactory;

    /**
     * @param InboundFeedFactory $inboundFeedFactory
     * @param LoggerInterface $logger
     * @param Csv $csvProcessor
     * @param File $file
     * @param DirectoryList $dir
     * @param Data $helper
     * @param GlobaloptFactory $globaloptFactory
     */
    public function __construct(
        InboundFeedFactory $inboundFeedFactory,
        LoggerInterface    $logger,
        Csv                $csvProcessor,
        File               $file,
        DirectoryList      $dir,
        Data               $helper,
        GlobaloptFactory   $globaloptFactory,
    ) {
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->csvProcessor = $csvProcessor;
        $this->file = $file;
        $this->dir = $dir;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->globaloptFactory = $globaloptFactory;
    }

    /**
     * Execute function
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if ($this->helper->isEnabled()) {
            $ssh = $this->getSFTPConnection();
            if (!$ssh->login($this->helper->getUserName(), $this->helper->getPassword())) {
                throw new LocalizedException(
                    new Phrase(sprintf(
                        "Unable to open SFTP connection as %s@%s",
                        $this->helper->getUserName(),
                        $this->helper->getHost()
                    ))
                );
            }
            $getPath = $ssh->pwd();
            $result = $ssh->nlist($ssh->pwd());
            $this->addUpdatedFeedRecord("Pending");
            foreach ($result as $filename) {
                if (stripos($filename, '.csv') !== false) {
                    try {
                        $targetPath = $this->dir->getPath('var') . '/' .  $filename;
                        $this->addUpdatedFeedRecord("Processing", $filename);
                        $ssh->get($getPath . '/' . $filename, $targetPath);
                        if ($this->file->isExists($targetPath)) {
                            $rowIndex = 1;
                            $csvData = $this->csvProcessor->getData($targetPath);
                            foreach ($csvData as $row => $data) {
                                if ($row > 0) {
                                    $email = $data[0] ?? null;
                                    if ($email) {
                                        $optOutModel = $this->globaloptFactory->create();
                                        $optOutModel->setEmail($email);
                                        $optOutModel->save();
                                    }
                                }
                                $rowIndex++;
                            }
                            $this->addUpdatedFeedRecord("Completed", $filename, "Total Records Inserted: " . $rowIndex);
                        } else {
                            $message = __("Global OptOut - No File exists to process");
                            $this->logger->critical($message);
                            $this->addUpdatedFeedRecord("Error", $filename, $message);
                        }
                    } catch (Exception $ex) {
                        $this->logger->critical(__("Exception while processing Global OptOut File : " . $filename));
                        $this->logger->critical($ex->getMessage());
                        $this->addUpdatedFeedRecord("Error", $filename, $ex->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Get SFTP Connection
     *
     * @return SFTP
     */
    public function getSFTPConnection(): SFTP
    {
        return new SFTP($this->helper->getHost(), $this->helper->getPort(), self::REMOTE_TIMEOUT);
    }

    /**
     * Add Updated Feed Record
     *
     * @param string $status
     * @param string|null $targetPath
     * @param string|null $message
     * @return void
     * @throws Exception
     */
    public function addUpdatedFeedRecord(string $status, string $targetPath = null, string $message = null): void
    {
        if (!$this->inboundFeedId) {
            $inboundFeed = $this->inboundFeedFactory->create();
            $inboundFeed->setFileName($targetPath);
            $inboundFeed->setStatus($status);
            $inboundFeed->setType("Global OptOut");
            $inboundFeed->save();
            $this->inboundFeedId = $inboundFeed->getId();
        } else {
            $inboundFeed = $this->inboundFeedFactory->create()->load($this->inboundFeedId);
            $inboundFeed->setFileName($targetPath);
            $inboundFeed->setStatus($status);
            $inboundFeed->setMessage($message);
            $inboundFeed->save();
        }
    }
}
