<?php

namespace Abbott\Chargeback\Helper;

use Abbott\Chargeback\Model\ResourceModel\Chargeback\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Io\File as IoFile;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public const EMAIL_TEMPLATE = 'chargeback_settings/chargeback_admin_email/chargeback_template';

    public const XML_PATH_EMAIL_SENDER = 'chargeback_settings/chargeback_admin_email/sender';

    public const RECEIVER_EMAIL = 'chargeback_settings/chargeback_admin_email/receiver_email';

    public const IS_MAIL_ENABLED = 'chargeback_settings/chargeback_admin_email/is_mail_enabled';

    public const KOUNT_EMAIL_TEMPLATE = 'chargeback_settings/kount_email_settings/kount_template';

    public const KOUNT_EMAIL_SENDER = 'chargeback_settings/kount_email_settings/sender';

    public const KOUNT_RECEIVER_EMAIL = 'chargeback_settings/kount_email_settings/receiver_email';

    public const KOUNT_MAIL_ENABLED = 'chargeback_settings/kount_email_settings/is_mail_enabled';

    public const LOG_LIFETIME = 'chargeback_settings/chargeback_file_settings/chargeback_log_lifetime';

    public const LOCAL_FILE_PATH = 'Abbott/Chargeback/PDE-0017/';

    public const ARCHIVE_FILE_PATH = 'chargeback_settings/chargeback_file_settings/archive_file_path';

    public const ARCHIVE_FILE_LIFETIME = 'chargeback_settings/chargeback_file_settings/archive_file_lifetime';

    public const SFTP_CHARGEBACK_PATH = 'chargeback_settings/chargeback_sftp/chargeback_path';

    public const SFTP_ARCHIVE_PATH = 'chargeback_settings/chargeback_sftp/archive_path';

    public const SFTP_CHARGEBACK_HOST = 'chargeback_settings/chargeback_sftp/host';

    public const SFTP_CHARGEBACK_PORT = 'chargeback_settings/chargeback_sftp/port';

    public const SFTP_CHARGEBACK_USERNAME = 'chargeback_settings/chargeback_sftp/username';

    public const SFTP_CHARGEBACK_ENCRYPTED = 'chargeback_settings/chargeback_sftp/password';

    public const SFTP_ZIP_ENCRYPTED = 'chargeback_settings/chargeback_sftp/zip_password';

    public const RECEIVER_NAME = 'ABBOTT';

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $transportBuilder;

    /**
     * @var File
     */
    protected File $file;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $chargebackCollection;

    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * @var Sftp
     */
    protected Sftp $sftp;

    /**
     * @var DateTime
     */
    protected DateTime $date;

    /**
     * @var IoFile
     */
    private IoFile $io;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param File $file
     * @param LoggerInterface $logger
     * @param DirectoryList $directoryList
     * @param IoFile $io
     * @param CollectionFactory $chargebackCollection
     * @param EncryptorInterface $encryptor
     * @param Sftp $sftp
     * @param DateTime $date
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        File $file,
        LoggerInterface $logger,
        DirectoryList $directoryList,
        IoFile $io,
        CollectionFactory $chargebackCollection,
        EncryptorInterface $encryptor,
        Sftp $sftp,
        DateTime $date
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->file = $file;
        $this->io = $io;
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->chargebackCollection = $chargebackCollection;
        $this->encryptor = $encryptor;
        $this->sftp = $sftp;
        $this->date = $date;
    }

    /**
     * Get Sender
     *
     * @return mixed
     */
    public function getSender()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER);
    }

    /**
     * Get Archive File Path
     *
     * @return mixed
     */
    public function getArchiveFilePath()
    {
        return $this->scopeConfig->getValue(self::ARCHIVE_FILE_PATH);
    }

    /**
     * Get Archive File Lifetime
     *
     * @return mixed
     */
    public function getArchiveFileLifetime()
    {
        return $this->scopeConfig->getValue(self::ARCHIVE_FILE_LIFETIME);
    }

    /**
     * Get To Mails
     *
     * @return string[]
     */
    public function getToMails()
    {
        return explode(",", $this->scopeConfig->getValue(self::RECEIVER_EMAIL));
    }

    /**
     * Get Email Template
     *
     * @return mixed
     */
    public function getEmailTemplate()
    {
        return $this->scopeConfig->getValue(self::EMAIL_TEMPLATE);
    }

    /**
     * Is Enabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::IS_MAIL_ENABLED);
    }

    /**
     * Get Kount Sender
     *
     * @return mixed
     */
    public function getKountSender()
    {
        return $this->scopeConfig->getValue(self::KOUNT_EMAIL_SENDER);
    }

    /**
     * Get Kount To Mails
     *
     * @return mixed
     */
    public function getKountToMails()
    {
        return explode(",", $this->scopeConfig->getValue(self::KOUNT_RECEIVER_EMAIL));
    }

    /**
     * Get Kount Email Template
     *
     * @return mixed
     */
    public function getKountEmailTemplate()
    {
        return $this->scopeConfig->getValue(self::KOUNT_EMAIL_TEMPLATE);
    }

    /**
     * Is Kount Enabled
     *
     * @return mixed
     */
    public function isKountEnabled()
    {
        return $this->scopeConfig->getValue(self::KOUNT_MAIL_ENABLED);
    }

    /**
     * Get Log LifeTime
     *
     * @return mixed
     */
    public function getLogLifeTime()
    {
        return $this->scopeConfig->getValue(self::LOG_LIFETIME);
    }

    /**
     * Get Zip Password
     *
     * @return mixed
     */
    public function getZipPassword()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::SFTP_ZIP_ENCRYPTED));
    }

    /**
     * Get Host
     *
     * @return mixed
     */
    public function getHost()
    {
        return $this->scopeConfig->getValue(self::SFTP_CHARGEBACK_HOST);
    }

    /**
     * Get Port
     *
     * @return mixed
     */
    public function getPort()
    {
        return (int)$this->scopeConfig->getValue(self::SFTP_CHARGEBACK_PORT);
    }

    /**
     * Get Username
     *
     * @return mixed
     */
    public function getUserName()
    {
        return $this->scopeConfig->getValue(self::SFTP_CHARGEBACK_USERNAME);
    }

    /**
     * Get Password
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::SFTP_CHARGEBACK_ENCRYPTED));
    }

    /**
     * Get SFTP Path
     *
     * @return mixed
     */
    public function getSFTPPath()
    {
        return $this->scopeConfig->getValue(self::SFTP_CHARGEBACK_PATH);
    }

    /**
     * Get Archive SFTP Path
     *
     * @return mixed
     */
    public function getArchiveSFTPPath()
    {
        return $this->scopeConfig->getValue(self::SFTP_ARCHIVE_PATH);
    }

    /**
     * SFTP Validator
     *
     * @return string
     */
    public function sftpValidator()
    {
        try {
            $connectionArray = [
            "host" => $this->getHost() . ":" . $this->getPort(),
            "username" => $this->getUserName(),
            "password" => $this->getPassword()
            ];
            $connection = $this->sftp->open($connectionArray);
            return (empty($connection)) ? 'success' : 'Login Failed';
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }
    }

    /**
     * Get StoreId
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Send Email
     *
     * @param string $template
     * @param array $templateData
     * @param string $fileName
     * @param array|string $mail
     * @param string $sender
     * @param boolean $sendAttachment
     * @return void
     * @throws LocalizedException
     */
    public function sendEmail($template, $templateData, $fileName, $mail, $sender, $sendAttachment = false)
    {
        if (($sendAttachment && !$this->isKountEnabled()) || (!$sendAttachment && !$this->isEnabled())) {
            return;
        }
        try {
            $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->getStoreId(),
                 ]
            )->setTemplateVars(
                $templateData
            )->setFrom(
                $sender
            )->addTo(
                $mail
            );
            if ($sendAttachment && $this->checkFileExist($fileName)) {
                $filepath = $this->getChargebackFilePath() . $fileName;
                $body = $this->file->fileGetContents($filepath);
                $this->transportBuilder->addAttachment($body, $fileName, 'text/csv');
            }
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Move files to Archive and Delete 30 days older files
     *
     * @param string $fileName
     * @param boolean $reqStamp
     */
    public function chargebackFiles($fileName, $reqStamp)
    {
        $filePath = $this->getChargebackFilePath().$fileName;
        $path = $this->getVarPath().$this->getArchiveFilePath();
        if (!$this->file->isExists($path)) {
            $this->io->mkdir($path, 0755);
        }
        $fileArchivePath = $this->getChargebackArchiveFilePath($fileName, $reqStamp);
        if ($this->checkFileExist($fileName)) {
            $this->moveChargeBackFiles($filePath, $fileArchivePath, self::ARCHIVE_FILE_PATH);
        }
    }

    /**
     * For Getting old Order file path
     *
     * @return string
     */
    public function getChargebackFilePath()
    {
        $varPath = $this->getVarPath();
        return $varPath . self::LOCAL_FILE_PATH;
    }

    /**
     * For Getting Order Archive File Path
     *
     * @param string $fileName
     * @param boolean $reqStamp
     * @return string
     */
    public function getChargebackArchiveFilePath($fileName, $reqStamp)
    {
        $varpath = $this->getVarPath();
        $pathInfo = $this->io->getPathInfo($fileName);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $fname = $reqStamp ? $name.'_'.$this->date->timestamp().'.'.$extension : $fileName;
        return  $varpath . $this->getArchiveFilePath() . $fname;
    }

    /**
     * Check if File exists
     *
     * @param string $fileName
     * @return bool
     * @throws FileSystemException
     */
    public function checkFileExist($fileName)
    {
        $varPath = $this->getVarPath();
        $filepath = $varPath . self::LOCAL_FILE_PATH . $fileName;
        return $this->file->isExists($filepath);
    }

    /**
     * Get Var Directory Path
     *
     * @return string
     */
    public function getVarPath()
    {
        return $this->directoryList->getPath('var') . '/';
    }

    /**
     * Move ChargeBack Files
     *
     * @param string $filepath
     * @param string $filePathAchive
     * @param string $archiveFolder
     * @throws LocalizedException
     */
    public function moveChargeBackFiles($filepath, $filePathAchive, $archiveFolder): void
    {
        try {
            $days = $this->getArchiveFileLifetime();
            $varpath = $this->getVarPath();
            $dir = $varpath . $this->getArchiveFilePath();
            if ($this->file->isExists($dir)) {
                $files = $this->file->readDirectory($dir);
                foreach ($files as $fileOrg) {
                    if ($this->file->isFile($fileOrg)
                        && (($this->date->timestamp()-filemtime($fileOrg))>($days * 86400))
                    ) {
                        $this->file->deleteFile($fileOrg);
                    }
                }
            } else {
                throw new LocalizedException(
                    __("Cannot open archive directory")
                );
            }
            $this->file->rename($filepath, $filePathAchive);
        } catch (\Exception $e) {
            $message="Something went wrong while moving file to archive " . $e->getMessage();
            $this->logger->error('Error message', ['exception' => $message]);
            throw new LocalizedException(
                __(
                    'Something went wrong while moving file to archive. %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Delete Db Log
     *
     * @return void
     */
    public function deleteDbLog()
    {
        $dbLogDays = $this->getLogLifeTime();
        $days = '-' . $dbLogDays . ' day';
        $dateToFilter = date('Y-m-d', strtotime($days));
        $this->chargebackCollection->create()->addFieldToFilter('created_at', ['lt' => $dateToFilter])->walk('delete');
    }

    /**
     * Extract Zip file
     *
     * @param string $source
     * @param string $targetDir
     * @param string $password
     * @return void
     */
    public function extractZipFile($source, $targetDir, $password): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($source) === true) {
            $this->logger->critical("Password : ".$password);
            $zip->setPassword($password);
            $zip->extractTo($targetDir);
        }
        $zip->close();
    }

    /**
     * Get Path Info
     *
     * @param string $filepath
     * @return array
     */
    public function getPathInfo($filepath): array
    {
        return $this->io->getPathInfo($filepath);
    }
}
