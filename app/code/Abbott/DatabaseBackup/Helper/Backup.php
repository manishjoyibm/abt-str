<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Abbott\DatabaseBackup\Helper;

use Abbott\DatabaseBackup\Logger\Method\Logger;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Aws\S3\S3Client;
use Aws\S3\Exception as AwsS3Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\State;

class Backup extends AbstractHelper
{
    public const ENABLE = "database_backup/configuration/enable";
    public const AWS_KEY = "database_backup/configuration/s3_key";
    public const AWS_SECRET = "database_backup/configuration/s3_secret";
    public const AWS_BUCKET = "database_backup/configuration/bucket_name";
    public const AWS_REGION = "database_backup/configuration/aws_region";
    public const AWS_VERSION = "database_backup/configuration/version";
    public const AWS_SYNCED_FILE = "database_backup/status/last_synced_file";
    public const SYNCED_FILE_SIZE = "database_backup/status/file_size";
    public const SYNCED_DATE_TIME = "database_backup/status/synced_date";
    public const ERROR_NOTIFICATION = "database_backup/configuration/error_notification";
    public const DEFAULT_SCOPE_ID = 0;
    public const EXCEPTION = "Exception ";

    /**
     * @var StoreManagerInterface
     */
    public StoreManagerInterface $storeManager;

    /**
     * @var S3Client
     */
    public $s3Client;

    /**
     * @var File
     */
    protected File $fileDriver;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var WriterInterface
     */
    protected WriterInterface $writeConfig;

    /**
     * @var StateInterface
     */
    protected StateInterface $inlineTranslation;
    /**
     * @var Escaper
     */
    protected Escaper $escaper;
    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $transportBuilder;

    /** @var State **/
    private State $state;

    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * Backup constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param File $fileDriver
     * @param Logger $logger
     * @param WriterInterface $writeConfig
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param State $state
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        File $fileDriver,
        Logger $logger,
        WriterInterface $writeConfig,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        State $state,
        EncryptorInterface $encryptor
    ) {
        $this->storeManager = $storeManager;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
        $this->writeConfig = $writeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->state = $state;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     *  Init S3 client class
     *
     * @throws NoSuchEntityException
     */
    public function init(): void
    {
        if ($this->s3Client === null) {
            $key = $this->getDbBackupConfig(self::AWS_KEY);
            $secret = $this->getDecryptValue(self::AWS_SECRET);
            $version = $this->getDbBackupConfig(self::AWS_VERSION);
            $region = $this->getDbBackupConfig(self::AWS_REGION);

            $this->s3Client = new S3Client([
                'version' => $version,
                'region' => $region,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $key,
                    'secret' => $secret
                ]
            ]);
        }
    }

    /**
     * Get DB Backup Module Config
     *
     * @param string $path
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDbBackupConfig(string $path): mixed
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * Get Decrypted Value
     *
     * @param string $path
     * @return string
     */
    public function getDecryptValue(string $path): string
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue($path));
    }

    /**
     *  Function which transfer the data to AWS s3 bucket
     * @param $file
     * @param string $fileNameInS3
     * @param string $bucketName
     * @return bool
     * @throws FileSystemException
     */
    public function sendDataToS3($file, string $fileNameInS3, string $bucketName)
    {
        if ($this->fileDriver->isExists($file) && !empty($bucketName)) {
            try {
                if (empty($fileNameInS3)) {
                    $fileNameInS3 = basename($file);
                }
                $this->logger->info("AWS S3 Data Dump initiated. File Name=".$fileNameInS3);

                // Push the file to s3 bucket
                $this->s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $fileNameInS3,
                    'SourceFile' => $file,
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                ]);

                $this->s3Client->waitUntil("ObjectExists", [
                    'Bucket' => $bucketName,
                    'Key'    => $fileNameInS3
                ]);
                $this->logger->info("AWS S3 Data Dump finished successfully. File Name=".$fileNameInS3);
                return true;
            } catch (AwsS3Exception $ex) {
                $this->logger->critical("AWS Exception ".$ex->getMessage());
                $this->sendAlertEmail("AWS Exception ".$ex->getMessage());
                throw new AwsS3Exception(__('AWS S3 Exception : Unable to upload file'));
            } catch (FileSystemException $ex) {
                $this->logger->critical("File System Exception ".$ex->getMessage());
                $this->sendAlertEmail("File System Exception ".$ex->getMessage());
                throw new FileSystemException(__('AWS S3 Exception : Unable to upload file'));
            } catch (Exception $ex) {
                $this->logger->critical(self::EXCEPTION.$ex->getMessage());
                $this->sendAlertEmail(self::EXCEPTION.$ex->getMessage());
            }
        }
    }

    /** Function to store status of db sync in config
     *
     * @param string $filePath
     * @param string $fileName
     * @return bool
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function saveLastDumpData(string $filePath, string $fileName)
    {
        try {
            if ($this->fileDriver->isExists($filePath)) {
                $filesize = filesize($filePath); // bytes
                $fileSizeInMB = round($filesize / 1024 / 1024, 1);
                $this->writeConfig->save(
                    self::AWS_SYNCED_FILE,
                    $fileName,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    self::DEFAULT_SCOPE_ID
                );
                $this->writeConfig->save(
                    self::SYNCED_FILE_SIZE,
                    $fileSizeInMB,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    self::DEFAULT_SCOPE_ID
                );
                $this->writeConfig->save(
                    self::SYNCED_DATE_TIME,
                    date("Y-m-d H:i:s"),
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    self::DEFAULT_SCOPE_ID
                );
                $this->logger->info("Synced file status saved in config");
                return true;
            }
        } catch (FileSystemException $e) {
            $this->logger->critical(self::EXCEPTION.$e->getMessage());
            $this->sendAlertEmail(self::EXCEPTION.$e->getMessage());
            throw new FileSystemException(__('Exception: Unable read file'));
        } catch (NoSuchEntityException $e) {
            $this->logger->critical(self::EXCEPTION.$e->getMessage());
            $this->sendAlertEmail(self::EXCEPTION.$e->getMessage());
            throw new NoSuchEntityException(__('Exception: No such entity'));
        }
    }

    /** Function to send email while DB backup error occur
     *
     * @param $msg
     * @throws NoSuchEntityException
     */

    public function sendAlertEmail($msg): void
    {
        $emailIdData = $this->getDbBackupConfig(self::ERROR_NOTIFICATION);
        $emailIds = explode(",", $emailIdData);
        if (!empty($emailIds)) {
            try {
                $this->state->setAreaCode(Area::AREA_FRONTEND);
                $this->inlineTranslation->suspend();
                $sender = [
                'name' => $this->escaper->escapeHtml('Abbottstore'),
                'email' => $this->escaper->escapeHtml('noreply@abbott.com'),
                ];
                $transport = $this->transportBuilder
                ->setTemplateIdentifier('database_backup_configuration_error_template')
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => Store::DEFAULT_STORE_ID,
                    ]
                )
                    ->setTemplateVars([
                    'templateVar'  => $msg,
                ])
                    ->setFrom($sender)
                    ->addTo($emailIds)
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            } catch (Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }
}
