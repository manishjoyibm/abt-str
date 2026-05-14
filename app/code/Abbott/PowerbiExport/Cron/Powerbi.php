<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Abbott\PowerbiExport\Cron;


use Abbott\PowerbiExport\Logger\Method\Logger;
use Abbott\PowerbiExport\Model\PowerbiExport;
use Abbott\PowerbiExport\Helper\Powerbi as PowerbiHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Powerbi
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var PowerbiExport
     */
    protected $powerbiExport;
    /**
     * @var PowerbiHelper
     */
    private $powerbiHelper;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * Constructor
     * @param Logger $logger
     * @param PowerbiExport $powerbiExport
     * @param \Abbott\PowerbiExport\Helper\Powerbi $PowerbiHelper
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Logger $logger,
        PowerbiExport $powerbiExport,
        PowerbiHelper $powerbiHelper,
        Filesystem $fileSystem
    )
    {
        $this->logger = $logger;
        $this->powerbiExport = $powerbiExport;
        $this->powerbiHelper = $powerbiHelper;
        $this->fileSystem   = $fileSystem;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if ($this->powerbiHelper->getPowerbiConfig(PowerbiHelper::ENABLE_CRON_POWERBI_EXPORT)) {
            $this->logger->info("Export API Cronjob has started at ".date("m-d-y H:i:s"));
            try {
                if ($this->powerbiHelper->getPowerbiConfig(PowerbiHelper::ENABLE_POWERBI_EXPORT)) {
                    $this->powerbiExport->execute();
                    $timespan = $this->powerbiHelper->getPowerbiConfig(PowerbiHelper::MBI_EXPORT_DELETE_FILE_CONFIG);
                    if (!empty($timespan)) {
                        $this->deleteFiles($timespan);
                    }
                } else {
                    $this->logger->info("PowerBI report export functionality is disabled.");
                }
            }catch (\Exception $ex) {
                $this->logger->critical("Exception while executing cron job " . $ex->getMessage());
            }
            $this->logger->info("Export API Cronjob has ended at ".date("m-d-y H:i:s"));
        }
    }

    /**
     * Delete old files
     *
     * @param  string $interval
     * @return int
     * @throws LocalizedException
     */
    public function deleteFiles($interval)
    {
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $deletedFiles = 0;
        $absolutePath = $directory->getAbsolutePath();
        $mbiTmpPath = $absolutePath . PowerbiHelper::MBI_PATH;
        $interval = ($interval) ? strtotime($interval) : strtotime('-3 month'); //files older than timespan
        try {
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($mbiTmpPath, \RecursiveDirectoryIterator::KEY_AS_PATHNAME),
                \RecursiveIteratorIterator::CHILD_FIRST
            ) as $info) {
                // phpcs:ignore
                if ($info->isFile() &&
                    $info->isReadable() &&
                    filemtime($info->getPathname()) <= $interval &&
                    $directory->delete($info->getPathname())
                ) {
                    $deletedFiles++;
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Tmp file deletion error'. $e->getMessage());
        }
        $this->logger->info('Deleted Tmp Files : ' . $deletedFiles);
        return $deletedFiles;
    }
}