<?php

namespace Abbott\WorkdayFeed\Helper;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem\Io\Sftp;
use Psr\Log\LoggerInterface;

class InboundFeedHelper extends AbstractHelper
{
    public $encryptor;
    public $logger;
    public $sftp;
    public const ABBOTT_EMPLOYEE_GROUP_ID = 'workday_feed_settings/workday_sftp/emp_group_id_abbott';
    public const ABBVIE_EMPLOYEE_GROUP_ID = 'workday_feed_settings/workday_sftp/emp_group_id_abbvie';
    public const ALERE_EMPLOYEE_GROUP_ID = 'workday_feed_settings/workday_sftp/emp_group_id_alere';
    public const ABBOTT_COMPANY_NAME = "ABBOTT";
    public const ABBVIE_COMPANY_NAME = "ABBVIE";
    public const ALERE_COMPANY_NAME = "ALERE";
    public const ABBOTT_RETIREE_GROUP_ID = 'workday_feed_settings/workday_sftp/retiree_group_id_abbott';
    public const CONSUMER_GROUP_ID = 1;
    public const WD_COMPANY = 'wd_company';
    public const WD_UPI = 'wd_upi';
    public const WD_STATUS = 'wd_status';
    public const ACTIVE = 'A';
    public const RETIREE = "R";
    public const ADD = 'A';
    public const DELETE = 'D';
    public const MODIFY = 'M';
    public const NO_EXCEPTION = 'No Exceptions';
    public const SUCCESS_STATUS = 'Success';
    public const FAILURE_STATUS = 'Failed';
    public const EMPTY_LINE = 'Empty Line';
    public const SFTP_WORKDAY_HOST = 'workday_feed_settings/workday_sftp/host';
    public const SFTP_WORKDAY_PORT = 'workday_feed_settings/workday_sftp/port';
    public const SFTP_WORKDAY_USERNAME = 'workday_feed_settings/workday_sftp/username';
    public const SFTP_WORKDAY_ENCRYPTED = 'workday_feed_settings/workday_sftp/password';
    public const EMAIL_TEMPLATE = 'workday_feed_settings/workday_feed/workday_template';
    public const XML_PATH_EMAIL_SENDER = 'workday_feed_settings/workday_feed/sender';
    public const RECEIVER_EMAIL = 'workday_feed_settings/workday_feed/receiver_email';
    public const IS_ENABLED_MAIL = 'workday_feed_settings/workday_feed/is_enabled_mail';
    public const LIFETIME_IDX = 'workday_feed_settings/workday_feed/lifetime_idx';
    public const LIFETIME_FEED = 'workday_feed_settings/workday_feed/lifetime_feed';
    public const CUSTOMER_MAIL_ENABLED = 'workday_feed_settings/workday_feed/email_enabled';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_DECRYPTING = 'Decrypting';
    public const STATUS_PROCESED = 'Processed';
    public const STATUS_FAILED = 'Failed';
    public const MESSAGE_PENDING = 'No Records Yet Added';
    public const FILE_CONTENT_TYPE = 'Workday';
    public const UNKNOWN_RECORD = 'Unknown Record Status Found';
    public const FILE_PATH = "/Abbott/WorkdayFeed/";
    public const SFTP_ABBOTT_PATH = 'workday_feed_settings/workday_sftp/abbott_path';
    public const SFTP_ALERE_PATH = 'workday_feed_settings/workday_sftp/alere_path';
    public const SFTP_ABBVIE_PATH = 'workday_feed_settings/workday_sftp/abbvie_path';
    public const SFTP_ARCHIVE_PATH = 'workday_feed_settings/workday_sftp/archive_path';
    public const ABBOTT_PVT_KEY = 'workday_feed_settings/workday_keys/abbott_key';
    public const ABBVIE_PVT_KEY = 'workday_feed_settings/workday_keys/abbvie_key';
    public const ALERE_PVT_KEY = 'workday_feed_settings/workday_keys/alere_key';
    public const ABBOTT_PASSPHRASE = 'workday_feed_settings/workday_keys/abbott_passphrase';
    public const ABBVIE_PASSPHRASE = 'workday_feed_settings/workday_keys/abbvie_passphrase';
    public const ALERE_PASSPHRASE = 'workday_feed_settings/workday_keys/alere_passphrase';
    public const INVALID_COLUMN_ORDERS = 'invalid columns list';
    public const COLUMN_ONE_NAME = 'Record Status';
    public const COLUMN_TWO_NAME  = 'Status';
    public const COLUMN_THREE_NAME  = 'UPI';
    public const INBOUND_FEED_TABLE = 'apollo_inbound_feed';
    public const WORKDAY_IDX_TABLE = 'apollo_inbound_workday_idx';
    public const COLUMN_FOUR_NAME  = 'Last Name';
    public const COLUMN_FIVE_NAME  = 'First Name';
    public const COLUMN_SIX_NAME  = 'Middle Initial';
    public const COLUMN_SEVEN_NAME  = 'Company';
    public const COLUMN_EIGHT_NAME  = 'Email-ID';
    public const IDX_TABLE_COLUMN_ONE = 'record_status';
    public const IDX_TABLE_COLUMN_TWO = 'upi';
    public const IDX_TABLE_COLUMN_THREE = 'record';
    public const IDX_TABLE_COLUMN_FOUR = 'status';
    public const IDX_TABLE_COLUMN_FIVE = 'message';
    public const IDX_TABLE_COLUMN_SIX = 'feed_id';
    public const WORKDAY_CUSTOMER_EMAIL_TEMPLATE = 'workday_feed_settings/workday_sftp/workday_customer_template';

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface $logger
     * @param Sftp $sftp
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        LoggerInterface $logger,
        Sftp $sftp
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->sftp = $sftp;
    }

    /**
     * Get To Mails
     *
     * @return array|string|null
     */
    public function getToMails(): null | array | string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_EMAIL);
    }

    /**
     * Check if Enable
     *
     * @return int
     */
    public function isEnabled(): int
    {
        return $this->scopeConfig->getValue(self::IS_ENABLED_MAIL);
    }

    /**
     * Method lifeTimeIdx
     *
     * @return int
     */
    public function lifeTimeIdx(): int
    {
        return $this->scopeConfig->getValue(self::LIFETIME_IDX);
    }

    /**
     * Method lifeTimeFeed
     *
     * @return int
     */
    public function lifeTimeFeed(): int
    {
        return $this->scopeConfig->getValue(self::LIFETIME_FEED);
    }

    /**
     * Method customerEmailEnabled
     *
     * @return int
     */
    public function customerEmailEnabled(): int
    {
        return $this->scopeConfig->getValue(self::CUSTOMER_MAIL_ENABLED);
    }

    /**
     * Method getAbbottEmployeeGroupId
     *
     * @return int
     */
    public function getAbbottEmployeeGroupId(): int
    {
        return (int)$this->scopeConfig->getValue(self::ABBOTT_EMPLOYEE_GROUP_ID);
    }

    /**
     * Method getAbbvieEmployeeGroupId
     *
     * @return int
     */
    public function getAbbvieEmployeeGroupId(): int
    {
        return (int)$this->scopeConfig->getValue(self::ABBVIE_EMPLOYEE_GROUP_ID);
    }

    /**
     * Method getAlereEmployeeGroupId
     *
     * @return int
     */
    public function getAlereEmployeeGroupId(): int
    {
        return (int)$this->scopeConfig->getValue(self::ALERE_EMPLOYEE_GROUP_ID);
    }

    /**
     * Method getAbbottRetireeGroupId
     *
     * @return int
     */
    public function getAbbottRetireeGroupId(): int
    {
        return (int)$this->scopeConfig->getValue(self::ABBOTT_RETIREE_GROUP_ID);
    }

    /**
     * Get Host Details
     *
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->scopeConfig->getValue(self::SFTP_WORKDAY_HOST);
    }

    /**
     * Get SFTP Port detail
     *
     * @return int|string|null
     */
    public function getPort(): int|string|null
    {
        return (int)$this->scopeConfig->getValue(self::SFTP_WORKDAY_PORT);
    }

    /**
     * Get SFTP UserName
     *
     * @return string|null
     */
    public function getUserName(): ?string
    {
        return $this->scopeConfig->getValue(self::SFTP_WORKDAY_USERNAME);
    }

    /**
     * Get SFTP password
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::SFTP_WORKDAY_ENCRYPTED));
    }

    /**
     * Get Abbott SFTP Path
     *
     * @return string|null
     */
    public function getAbbottSFTPPath(): ?string
    {
        return $this->scopeConfig->getValue(self::SFTP_ABBOTT_PATH);
    }

    /**
     * Get Abbvie SFTP Path
     *
     * @return string|null
     */
    public function getAbbvieSFTPPath(): ?string
    {
        return $this->scopeConfig->getValue(self::SFTP_ABBVIE_PATH);
    }

    /**
     * Get Alere SFTP Path
     *
     * @return string|null
     */
    public function getAlereSFTPPath(): ?string
    {
        return $this->scopeConfig->getValue(self::SFTP_ALERE_PATH);
    }

    /**
     * Get Archive SFTP Path
     *
     * @return string|null
     */
    public function getArchiveSFTPPath(): ?string
    {
        return $this->scopeConfig->getValue(self::SFTP_ARCHIVE_PATH);
    }

    /**
     * Method SFTPValidator
     *
     */
    public function SFTPValidator()
    {
        try {
            $connectionArray = [
            "host" => $this->getHost() . ":" . $this->getPort(),
            "username" => $this->getUserName(),
            "password" => $this->getPassword()
            ];
            $connection = $this->sftp->open($connectionArray);
            return (empty($connection)) ? 'success' : 'Login Failed';
        } catch (Exception $ex) {
            $this->logger->info($ex->getMessage());
        }
    }

    /**
     * Method getAbbottPvtKey
     *
     * @return string|null
     */
    public function getAbbottPvtKey(): ?string
    {
        return $this->scopeConfig->getValue(self::ABBOTT_PVT_KEY);
    }

    /**
     * Method getAbbviePvtKey
     *
     * @return string|null
     */
    public function getAbbviePvtKey(): ?string
    {
        return $this->scopeConfig->getValue(self::ABBVIE_PVT_KEY);
    }

    /**
     * Method getAlerePvtKey
     *
     * @return string|null
     */
    public function getAlerePvtKey(): ?string
    {
        return $this->scopeConfig->getValue(self::ALERE_PVT_KEY);
    }

    /**
     * Method getAbbottPassphrase
     *
     * @return string|null
     */
    public function getAbbottPassphrase(): ?string
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::ABBOTT_PASSPHRASE));
    }

    /**
     * Method getAbbviePassphrase
     *
     * @return string|null
     */
    public function getAbbviePassphrase(): ?string
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::ABBVIE_PASSPHRASE));
    }

    /**
     * Method getAlerePassphrase
     *
     * @return string|null
     */
    public function getAlerePassphrase(): ?string
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::ALERE_PASSPHRASE));
    }
}
