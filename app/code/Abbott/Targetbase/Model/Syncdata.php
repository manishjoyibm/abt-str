<?php
namespace Abbott\Targetbase\Model;

use Magento\Setup\Exception;
use phpseclib3\Crypt\RSA;


class Syncdata extends \Magento\Framework\Model\AbstractModel
{
    const SFTP_ARCHIVE_PATH_ORDER = 'targetbase_integration/targetbase/targetbase_sftp_archive_folder_order';
    const SFTP_ARCHIVE_PATH_CUSTOMER = 'targetbase_integration/targetbase/targetbase_sftp_archive_folder_customer';
    const SFTP_TARGETBASE_HOST = 'targetbase_integration/targetbase/targetbase_sftp_host';
    const SFTP_TARGETBASE_PORT = 'targetbase_integration/targetbase/targetbase_sftp_port';
    const SFTP_TARGETBASE_USERNAME = 'targetbase_integration/targetbase/targetbase_sftp_username';
    const SFTP_TARGETBASE_SEC = 'targetbase_integration/targetbase/targetbase_sftp_password';
    const EMAIL_ENABLE = 'targetbase_integration/targetbase_email_template/is_enabled';
    const RECEIVER_EMAIL = 'targetbase_integration/targetbase_email_template/receiver_email';
    const EMAIL_TEMPLATE = 'targetbase_integration/targetbase_email_template/email_template';
    const EMAIL_SENDER = 'targetbase_integration/targetbase_email_template/sender';
    const STATUS_PENDING = 'Pending';
    const MESSAGE_PENDING = 'No Records Processed';
    const STATUS_SUCCESS = 'Success';
    const STATUS_FAILURE = 'Failure';
    const FILE_CONTENT_TYPE = 'Targetbase';
    const CUSTOMER_FILE_NAME = 'TB Customer Sync';
    const ORDER_FILE_NAME = 'TB Purchase Sync';
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
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptorInterface;
    /**
     * @var Exportdata
     */
    protected $customerDataModel;
    /**
     * @var Exportorderdata
     */
    protected $orderDataModel;
    /**
     * @var \Abbott\WorkdayFeed\Model\InboundFeedFactory
     */
    protected $inboundFeedFactory;
    /**
     * @var \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory
     */
    protected $inboundFeedCollection;
    /**
     * @var \Magento\Framework\Filesystem\Io\Sftp
     */
    protected $sftp;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * Syncdata constructor.
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface
     * @param Exportdata
     * @param Exportorderdata
     * @param \Abbott\WorkdayFeed\Model\InboundFeedFactory $inboundFeedFactory
     * @param \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory $inboundFeedCollection
     * @param \Magento\Framework\Filesystem\Io\Sftp $sftp
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        Exportdata $customerDataModel,
        Exportorderdata $orderDataModel,
        \Abbott\WorkdayFeed\Model\InboundFeedFactory $inboundFeedFactory,
        \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory $inboundFeedCollection,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->directoryList = $directoryList;
        $this->_scopeConfig = $scopeConfig;
        $this->encryptorInterface = $encryptorInterface;
        $this->customerDataModel = $customerDataModel;
        $this->orderDataModel = $orderDataModel;
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->inboundFeedCollection = $inboundFeedCollection;
        $this->sftp = $sftp;
        $this->_storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * This function is used sync both customer and order files
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return void
     */
    public function syncAllData()
    {
        $customerData = [self::FILE_CONTENT_TYPE, self::CUSTOMER_FILE_NAME,
            self::STATUS_PENDING, self::MESSAGE_PENDING];
        $inboundFeed = $this->inboundFeedFactory->create();
        $inboundFeed->submitReport($customerData);
        $orderData = [self::FILE_CONTENT_TYPE, self::ORDER_FILE_NAME, self::STATUS_PENDING, self::MESSAGE_PENDING];
        $inboundFeed = $this->inboundFeedFactory->create();
        $inboundFeed->submitReport($orderData);
        $archiveFolderCustomer = $this->_scopeConfig->getValue(self::SFTP_ARCHIVE_PATH_CUSTOMER);
        $archiveFolderOrder = $this->_scopeConfig->getValue(self::SFTP_ARCHIVE_PATH_ORDER);
        $fileName = $this->customerDataModel->getLastCustomerFile();
        $filepath= $this->customerDataModel->getOldCustomerFilePath();
        $fileNameOrder = $this->orderDataModel->getLastOrderFile();
        $filepathOrder= $this->orderDataModel->getOldOrderFilePath();
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
            $open = $this->sftp->open($connectionArray);
            if ($open=="") {
                try {
                    $customerfileexist = $this->customerDataModel->checkFileExists();
                    if ($customerfileexist) {
                        $this->sftp->write($archiveFolderCustomer . $fileName, $filepath);
                        $updateMessage = "Encrypted file posted to SFTP location";
                        $inboundFeed->updateReport($this->getCustomerInboundId(), self::STATUS_SUCCESS, $updateMessage);
                    } else {
                        $updateMessage="Customer file does not exist";
                        $inboundFeed->updateReport($this->getCustomerInboundId(), self::STATUS_FAILURE, $updateMessage);
                        $this->getSendEmail($updateMessage);
                    }
                } catch (Exception $e) {
                    $updateMessage=$e->getMessage();
                    $inboundFeed->updateReport($this->getCustomerInboundId(), self::STATUS_FAILURE, $updateMessage);
                    $this->getSendEmail($updateMessage);
                }
                try {
                    $orderfileexist = $this->orderDataModel->checkFileExists();
                    if ($orderfileexist) {
                        $this->sftp->write($archiveFolderOrder . $fileNameOrder, $filepathOrder);
                        $inboundFeed->updateReport($this->getOrderInboundId(), self::STATUS_SUCCESS, $updateMessage);
                    } else {
                        $updateMessage="Order File does not exist";
                        $inboundFeed->updateReport($this->getOrderInboundId(), self::STATUS_FAILURE, $updateMessage);
                        $this->getSendEmail($updateMessage);
                    }
                } catch (Exception $e) {
                    $updateMessage=$e->getMessage();
                    $inboundFeed->updateReport($this->getOrderInboundId(), self::STATUS_FAILURE, $updateMessage);
                    $this->getSendEmail($updateMessage);
                }
            } else {
                $updateMessage="ssh not connected";
                $inboundFeed->updateReport($this->getCustomerInboundId(), self::STATUS_FAILURE, $updateMessage);
                $inboundFeed->updateReport($this->getOrderInboundId(), self::STATUS_FAILURE, $updateMessage);
                $this->getSendEmail($updateMessage);
            }
        } catch (Exception $e) {
            $updateMessage=$e->getMessage();
            $inboundFeed->updateReport($this->getCustomerInboundId(), self::STATUS_FAILURE, $updateMessage);
            $inboundFeed->updateReport($this->getOrderInboundId(), self::STATUS_FAILURE, $updateMessage);
            $this->getSendEmail($updateMessage);
        }
    }

    /**
     * To Decrypt Password
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
    private function getCustomerInboundId()
    {
        $model = $this->inboundFeedCollection->create()->addFieldToFilter(
            'file_name',
            ['eq' => self::CUSTOMER_FILE_NAME]
        )->addFieldToFilter(
            'status',
            ['eq' => self::STATUS_PENDING]
        )->getFirstItem();
        return $model->getFeedId();
    }
    private function getOrderInboundId()
    {
        $model = $this->inboundFeedCollection->create()->addFieldToFilter(
            'file_name',
            ['eq' => self::ORDER_FILE_NAME]
        )->addFieldToFilter('status', ['eq' => self::STATUS_PENDING])->getFirstItem();
        return $model->getFeedId();
    }
    private function getSendEmail($msg)
    {
        if ($this->_scopeConfig->getValue(self::EMAIL_ENABLE)) {
            $mails = explode(",", $this->_scopeConfig->getValue(self::RECEIVER_EMAIL));
            foreach ($mails as $mail) {
                $this->sendEmail($this->emailTemplateData($msg), $mail);
            }
        }
    }

    private function emailTemplateData($msg)
    {
        return [
            'msg' => $msg
        ];
    }
    private function sendEmail($templateData, $mail)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $template = $this->_scopeConfig->getValue(self::EMAIL_TEMPLATE);
        $sender = $this->_scopeConfig->getValue(self::EMAIL_SENDER);
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ]
        )->setTemplateVars(
            $templateData
        )->setFrom(
            $sender
        )->addTo(
            $mail,
            $this->_scopeConfig->getValue(self::RECEIVER_EMAIL)
        )->getTransport();
        $transport->sendMessage();
    }

    /**
     * This function is used sync Customer File Generated from Console script
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return void
     */
    public function syncCustomerFile($fileName = null)
    {
        $customerData = [
            self::FILE_CONTENT_TYPE,
            self::CUSTOMER_FILE_NAME,
            self::STATUS_PENDING,
            self::MESSAGE_PENDING
        ];
        $inboundFeed = $this->inboundFeedFactory->create();
        $inboundFeed->submitReport($customerData);
        $archiveFolderCustomer = $this->_scopeConfig->getValue(self::SFTP_ARCHIVE_PATH_CUSTOMER);
        $fileName = ($fileName) ? $fileName . '.pgp' : null;
        $filepath = $this->customerDataModel->getCustomerFilePathConsole($fileName);
        $connectionArray = [
            "host" => $this->_scopeConfig->getValue(self::SFTP_TARGETBASE_HOST) . ":" .
                $this->_scopeConfig->getValue(self::SFTP_TARGETBASE_PORT),
            "username" => $this->_scopeConfig->getValue(self::SFTP_TARGETBASE_USERNAME),
            "password" => RSA::loadPrivateKey(
                $this->getPrivateKey(),
                $this->getPrivateKeyPass()
            ),
            'timeout' => $this->_scopeConfig->getValue(self::SFTP_TARGETBASE_TIMEOUT)
        ];

        try {
            $open = $this->sftp->open($connectionArray);
            if ($open == "") {
                try {
                    $customerfileexist = $this->customerDataModel->checkCustomerFileExistsConsole($fileName);
                    if ($customerfileexist) {
                        $this->sftp->write($archiveFolderCustomer . $fileName, $filepath);
                        $updateMessage = "Encrypted file posted to SFTP location";
                        $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_SUCCESS, $updateMessage);
                    } else {
                        $updateMessage = "Customer file does not exist";
                        $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
                    }
                } catch (Exception $e) {
                    $updateMessage = $e->getMessage();
                    $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
                }
            } else {
                $updateMessage = "ssh not connected";
                $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);
            }
        } catch (Exception $e) {
            $updateMessage = $e->getMessage();
            $inboundFeed->updateReport($inboundFeed->getId(), self::STATUS_FAILURE, $updateMessage);

        }
    }
}