<?php

declare(strict_types=1);

namespace Abbott\DatabaseBackup\Model;

use Abbott\DatabaseBackup\Helper\Backup;
use Abbott\DatabaseBackup\Logger\Method\Logger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Abbott\DatabaseBackup\Model\DatabaseConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem;
use Magento\Framework\Shell;
use Magento\Framework\App\Filesystem\DirectoryList;

class DBBackup
{
    public $_varDirectory;
    public const DUMP_FILE_NAME_TEMPLATE = 'db-dump-%s.sql.gz';
    public const FILE_MATCHING_STRING = 'db-dump-*.sql.gz';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Backup
     */
    private $backupHelper;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * Construct function
     *
     * @param Logger $logger
     * @param Backup $backup
     * @param DeploymentConfig $deploymentConfig
     * @param \Abbott\DatabaseBackup\Model\DatabaseConnection $databaseConnection
     * @param Shell $shell
     * @param Filesystem $filesystem
     * @throws FileSystemException
     */
    public function __construct(
        Logger $logger,
        Backup $backup,
        DeploymentConfig $deploymentConfig,
        DatabaseConnection $databaseConnection,
        Shell $shell,
        Filesystem $filesystem
    ) {
        $this->logger = $logger;
        $this->backupHelper = $backup;
        $this->deploymentConfig = $deploymentConfig;
        $this->databaseConnection = $databaseConnection;
        $this->shell = $shell;
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Function which trigger DB backup process
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            $this->logger->info(sprintf('Start creation DB dump for database...'));

            $dumpFileName = sprintf(self::DUMP_FILE_NAME_TEMPLATE, time());
            $varDirectory = $this->_varDirectory->getAbsolutePath();
            $dumpFile = $varDirectory . $dumpFileName;

            $command = $this->prepareDatabaseDump();
            $command .= ' | gzip > ' . $dumpFile;

            $this->shell->execute('bash -c "set -o pipefail; ' . $command . '"');
            $this->logger->info(sprintf('Finished DB dump for database, it can be found here: %s', $dumpFile));

        } catch (LocalizedException $e) {
            $this->deleteTempDumpFile($dumpFile);
            $this->backupHelper->sendAlertEmail("exception while executing command " . $e->getMessage());
            return $this->logger->critical("exception while executing command " . $e->getMessage());
        } catch (\Exception $e) {
            $this->deleteTempDumpFile($dumpFile);
            $this->backupHelper->sendAlertEmail("exception while creating db dump file " . $e->getMessage());
            return $this->logger->critical("exception while creating db dump file " . $e->getMessage());
        }
        // code to check if dump file is generated or not
        $varDirectory = $this->_varDirectory->getAbsolutePath();
        $dbDumpList = glob($varDirectory . self::FILE_MATCHING_STRING);

        $this->sendFileToS3Bucket($dbDumpList);
        return $this;
    }


    /**
     * Prepare database dump
     *
     * @return string
     * @throws FileSystemException
     * @throws RuntimeException
     */
    protected function prepareDatabaseDump(): string
    {
        $hostName = $this->databaseConnection->getHostName();
        $dataBaseName = $this->databaseConnection->getDatabaseName();
        $userName = $this->databaseConnection->getUserName();
        $password = $this->databaseConnection->getPassword();
        $port = $this->databaseConnection->getPort();
        return $this->prepareSql($hostName, $dataBaseName, $userName, $password, $port);
    }

    /**
     * PrepareSql function
     *
     * @param string $hostName
     * @param string $dataBaseName
     * @param string $userName
     * @param string $password
     * @param string $port
     * @return string
     */
    public function prepareSql($hostName, $dataBaseName, $userName, $password, $port): string
    {
        $portString = null;
        if (!empty($port)) {
            $portString = " -P" . escapeshellarg($port);
        }
        return "mysqldump -h " . escapeshellarg($hostName) .
        " -u " . escapeshellarg($userName) . " -p" . escapeshellarg($password) .
        "$portString " . escapeshellarg($dataBaseName) . " --single-transaction --no-autocommit --quick";
    }

   /**
    * Send File To S3 Bucket
    *
    * @param $files
    * @return void
    * @throws NoSuchEntityException
    */
    protected function sendFileToS3Bucket($files = [])
    {
        // initialize the AWS client object
        $this->backupHelper->init();
        $bucketName = $this->backupHelper->getDbBackupConfig(Backup::AWS_BUCKET);
        if (is_array($files) && !empty($files)) {
            foreach ($files as $dumpFile) {
                try {
                    $fileName = basename($dumpFile);
                    $this->backupHelper->sendDataToS3($dumpFile, $fileName, $bucketName);
                    $this->backupHelper->saveLastDumpData($dumpFile, $fileName);
                    $this->deleteTempDumpFile($dumpFile);
                } catch (FileSystemException $e) {
                    $this->deleteTempDumpFile($dumpFile);
                    $this->logger->critical($e->getMessage());
                    $this->backupHelper->sendAlertEmail("exception: " . $e->getMessage());
                } catch (\Exception $e) {
                    $this->deleteTempDumpFile($dumpFile);
                    $this->logger->critical("exception while sending to s3 bucket".$e->getMessage());
                    $this->backupHelper->sendAlertEmail("exception while sending to s3 bucket".$e->getMessage());
                }
            }
        } else {
            $this->logger->info("There are no files generated in tmp directory");
        }
    }

    /**
     * Delete Temp Dump File
     *
     * @param string $filePath
     * @return void
     * @throws NoSuchEntityException
     */
    protected function deleteTempDumpFile($filePath)
    {
        if (file_exists($filePath)) {
            try {
                $this->shell->execute("rm -rf " . $filePath);
            } catch (LocalizedException $e) {
                $this->logger->critical($e->getMessage());
                $this->backupHelper->sendAlertEmail("Exception while deleting the files".$e->getMessage());
            }
        }
    }
}
