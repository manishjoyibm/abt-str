<?php
namespace Abbott\Targetbase\Model;

use Magento\Setup\Exception;
use phpseclib3\Crypt\RSA;

class Connectioncheck extends \Magento\Framework\Model\AbstractModel
{
    public $_scopeConfig;
    const SFTP_ARCHIVE_PATH_ORDER = 'targetbase_integration/targetbase/targetbase_sftp_archive_folder_order';
    const SFTP_ARCHIVE_PATH_CUSTOMER = 'targetbase_integration/targetbase/targetbase_sftp_archive_folder_customer';
    const SFTP_TARGETBASE_HOST = 'targetbase_integration/targetbase/targetbase_sftp_host';
    const SFTP_TARGETBASE_PORT = 'targetbase_integration/targetbase/targetbase_sftp_port';
    const SFTP_TARGETBASE_USERNAME = 'targetbase_integration/targetbase/targetbase_sftp_username';
    const SFTP_TARGETBASE_PRIVATE_KEY = 'targetbase_integration/targetbase/targetbase_sftp_private_key';
    const SFTP_TARGETBASE_PRIVATE_KEY_PASS = 'targetbase_integration/targetbase/targetbase_sftp_private_password';
    const SFTP_TARGETBASE_TIMEOUT = 'targetbase_integration/targetbase/timeout';

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptorInterface;
    /**
     * @var \Magento\Framework\Filesystem\Io\Sftp
     */
    protected $sftp;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Syncdata constructor.
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface
     * @param \Magento\Framework\Filesystem\Io\Sftp $sftp
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        \Psr\Log\LoggerInterface $logger,
    ) {
        $this->directoryList = $directoryList;
        $this->_scopeConfig = $scopeConfig;
        $this->encryptorInterface = $encryptorInterface;
        $this->sftp = $sftp;
        $this->logger = $logger;
    }

    /**
     * This function is used check if SFTP connection is working or not
     *
     */

    public function connectionCheckStatus(): void
    {
        try {
            RSA::loadPrivateKey(
                $this->getPrivateKey(),
                $this->getPrivateKeyPass()
            );
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit();
        }

        $connectionArray=[
            "host"=>$this->_scopeConfig->getValue(self::SFTP_TARGETBASE_HOST) . ":" .
                $this->_scopeConfig->getValue(self::SFTP_TARGETBASE_PORT),
            "username"=>$this->_scopeConfig->getValue(self::SFTP_TARGETBASE_USERNAME),
            "password"=>RSA::loadPrivateKey(
                $this->getPrivateKey(),
                $this->getPrivateKeyPass()
            ),
            'timeout' => $this->_scopeConfig->getValue(self::SFTP_TARGETBASE_TIMEOUT)
        ];

        try {
            $connection = $this->sftp->open($connectionArray);
            if (empty($connection)) {
                echo "SFTP connection working properly.";
            } else {
                echo "Something went wrong.";
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * To Decrypt Private Key
     * @return string
     */
    protected function getPrivateKey(): string
    {
        return $this->_scopeConfig->getValue(self::SFTP_TARGETBASE_PRIVATE_KEY);
    }

    /**
     * To Decrypt Private Key Password
     * @return string
     */
    protected function getPrivateKeyPass(): string
    {
        $encryptedPass = $this->_scopeConfig->getValue(self::SFTP_TARGETBASE_PRIVATE_KEY_PASS);
        return $this->encryptorInterface->decrypt($encryptedPass);
    }
}
