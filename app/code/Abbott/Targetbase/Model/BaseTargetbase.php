<?php
namespace Abbott\Targetbase\Model;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\TestFramework\Inspection\Exception;

class BaseTargetbase extends \Magento\Framework\Model\AbstractModel
{
    public $_scopeConfig;
    public $_targetbasefactory;
    const EMAIL_ENABLE = 'targetbase_integration/targetbase_email_template/is_enabled';
    const RECEIVER_EMAIL = 'targetbase_integration/targetbase_email_template/receiver_email';
    const EMAIL_TEMPLATE = 'targetbase_integration/targetbase_email_template/email_template';
    const REPORT_EMAIL_TEMPLATE = 'targetbase_integration/targetbase_email_template/report_email_template';
    const EMAIL_SENDER = 'targetbase_integration/targetbase_email_template/sender';
    const PUBLICKEY = 'targetbase_integration/targetbase/targetbase_pgp_public_key';
    const ARCHIVE_DELDAYS = 'targetbase_integration/targetbase/targetbase_archive_folder_deldays';
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptorInterface;
    /**
     * @var \Magento\Framework\Shell
     */
    protected $shell;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory
     */
    protected $inboundFeedCollection;
    /**
     * @var ResourceModel\Targetbase\CollectionFactory
     */
    protected $targetbasecollection;
    /**
     * @var TargetbaseFactory
     */
    protected $targetbasefactory;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var TargetbaseOrderFactory
     */
    protected $targetbaseOrderFactory;


    /**
     * @var \Abbott\OrderManagement\Helper\Data
     */
    protected $orderManagementHelper;

    /**
     * Exportdata constructor.
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface
     * @param \Magento\Framework\Shell
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory $inboundFeedCollection
     * @param ResourceModel\Targetbase\CollectionFactory $targetbasecollection
     * @param TargetbaseFactory $targetbasefactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Psr\Log\LoggerInterface $logger
     * @param TargetbaseOrderFactory $targetbaseOrderFactory
     * @param \Abbott\OrderManagement\Helper\Data $orderManagementHelper
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Framework\Shell $shell,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory $inboundFeedCollection,
        ResourceModel\Targetbase\CollectionFactory $targetbasecollection,
        TargetbaseFactory $targetbasefactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Psr\Log\LoggerInterface $logger,
        TargetbaseOrderFactory $targetbaseOrderFactory,
        \Abbott\OrderManagement\Helper\Data $orderManagementHelper
    ) {
        $this->directoryList = $directoryList;
        $this->transportBuilder = $transportBuilder;
        $this->encryptorInterface = $encryptorInterface;
        $this->shell = $shell;
        $this->_scopeConfig = $scopeConfig;
        $this->inboundFeedCollection = $inboundFeedCollection;
        $this->targetbasecollection = $targetbasecollection;
        $this->_targetbasefactory = $targetbasefactory;
        $this->file = $file;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->logger = $logger;
        $this->targetbaseOrderFactory = $targetbaseOrderFactory;
        $this->orderManagementHelper = $orderManagementHelper;
    }
    /**
     * To get the correct inbound id
     *
     * @return mixed
     */
    public function getInboundId($filename)
    {
        $model = $this->inboundFeedCollection->create()
            ->addFieldToFilter('file_name', ['eq' => $filename])
            ->addFieldToFilter('status', ['eq' => 'Pending'])->getFirstItem();
        return $model->getFeedId();
    }
    /**
     * To send Email with Error Message
     *
     * @param string $msg Error Message
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSendEmail($msg)
    {
        if ($this->_scopeConfig->getValue(self::EMAIL_ENABLE)) {
            $mails = explode(",", $this->_scopeConfig->getValue(self::RECEIVER_EMAIL));
            foreach ($mails as $mail) {
                $this->sendEmail($this->emailTemplateData($msg), $mail);
            }
        }
    }

    /**
     * To send Email with Customer Export Message
     *
     * @param string $msg Report Message
     * @param string $sub Report Subject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSendExportEmail($msg, $sub)
    {
        if ($this->_scopeConfig->getValue(self::EMAIL_ENABLE)) {
            $mails = explode(",", $this->_scopeConfig->getValue(self::RECEIVER_EMAIL));
            foreach ($mails as $mail) {
                $this->sendExportEmail($this->reportEmailTemplateData($msg, $sub), $mail);
            }
        }
    }


    /**
     * For Sending Error Message to The Template
     *
     * @param string $msg Error Message
     *
     * @return array
     */
    private function emailTemplateData($msg)
    {
        return [
            'msg' => $msg
        ];
    }

    /**
     * For Sending Message to The Template
     *
     * @param string $msg Message
     * @param string $sub Subject
     * @return array
     */
    private function reportEmailTemplateData($msg, $sub)
    {
        return [
            'msg' => $msg,
            'sub' => $sub
        ];
    }
    /**
     * For Sending Email If any Error
     *
     * @param array  $templateData Error Message
     * @param string $mail         Email Id
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return void
     */
    private function sendEmail($templateData, $mail)
    {
        $storeId = $this->storeManagerInterface->getStore()->getId();
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
     * For Sending Customer Export Report
     *
     * @param array  $templateData Message
     * @param string $mail         Email Id
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return void
     */
    private function sendExportEmail($templateData, $mail)
    {

        $storeId = $this->storeManagerInterface->getStore()->getId();
        $template = $this->_scopeConfig->getValue(self::REPORT_EMAIL_TEMPLATE);
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
     * For generating path till var folder with /
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getVarPath()
    {
        return $this->directoryList->getRoot() . '/var/';
    }

    /**
     * For Moving File to Archive and Delete 30 days older files
     *
     * @param string $filepath the main file path
     * @param string $filePathAchive the archive file path
     *
     * @param $archiveFolder
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function moveFiles($filepath, $filePathAchive, $archiveFolder)
    {
        try {
            $days = $this->_scopeConfig->getValue(self::ARCHIVE_DELDAYS);
            $oldDays = '-' . $days . ' day';
            $date = date('Y-m-d', strtotime($oldDays));
            $this->targetbasecollection->create()
            ->addFieldToFilter('status', 'success')
            ->addFieldToFilter('created_at', ['lt' => $date])
            ->walk('delete');
            $varpath = $this->getVarPath();
            $dir = $varpath . $this->_scopeConfig->getValue($archiveFolder);

            $nofiles = 0;

            if ($handle = opendir($dir)) {
                while (($fileOrg = readdir($handle)) !== false) {
                    if ($fileOrg == '.' || $fileOrg == '..' || $this->file->isDirectory($dir . '/' . $fileOrg)) {
                        continue;
                    }

                    if ((time() - filemtime($dir . '/' . $fileOrg)) > ($days * 86400)) {
                        $nofiles++;
                        unlink($dir . '/' . $fileOrg);
                    }
                }
                closedir($handle);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException("Cannot open archive directory");
            }
            $this->file->rename($filepath, $filePathAchive);
        } catch (\Exception $e) {
            $message="Something went wrong while moving file to archive " . $e->getMessage();
            $this->logger->error('Error message', ['exception' => $message]);
            $this->getSendEmail($message);
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Something went wrong while moving file to archive. %1',
                    $e->getMessage()
                )
            );
        }
    }
    /**
     * To get the location of the keyfile that has been generated
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return string
     */
    public function getKeyFilePath()
    {
        $keydata=$this->_scopeConfig->getValue(self::PUBLICKEY);
        $varPath = $this->getVarPath();
        $keyfilepath = $varPath . 'Targetbase/' . 'targetbase_import-public.key';
        $fileExists = $this->file->isExists($keyfilepath);
        if ($fileExists==0) {
            $keyfile = $this->file->fileOpen($keyfilepath, "w");
            $this->file->fileWrite($keyfile, $keydata);
            $this->file->fileClose($keyfile);
        }
        return $keyfilepath;
    }

    /**
     * Encrypt File to PGP
     *
     * @param string $filepath      Actual File Path
     * @param string $recipientdata recipient name from system config
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function encryptFile($filepath, $recipientdata)
    {
        putenv("GNUPGHOME=/tmp");
        $keyfilepath = $this->getKeyFilePath();
        $command = 'gpg --import ' . $keyfilepath;
        $this->shell->execute($command);
        $recipientEnc = $this->_scopeConfig->getValue($recipientdata);
        $recipient = $this->encryptorInterface->decrypt($recipientEnc);
        $command = "gpg --batch --always-trust --recipient " . $recipient . " --encrypt " . $filepath;
        $this->shell->execute($command);
        $this->file->rename($filepath . '.gpg', $filepath . '.pgp');
        $this->file->deleteFile($filepath);
    }

    /**
     * For Adding Data to our collection
     *
     * @param $customerId
     * @param $storeId
     * @param $type
     * @param $orderid
     * @param null $status
     *
     * @return void
     * @throws \Exception
     */
    public function insertData($customerId, $storeId, $type, $orderid, $status = null)
    {
        $targetbasedata = $this->_targetbasefactory->create();
        $targetbasedata->setCustomerId($customerId);
        $targetbasedata->setStoreId($storeId);
        ($type=="register_subscription" || $type=="subscription") ?
            $targetbasedata->setContactPreference(1) : $targetbasedata->setContactPreference(0);
        ($type=="register_subscription" || $type=="register") ?
            $targetbasedata->setIsRegistration(1) : $targetbasedata->setIsRegistration(0);
        ($type=="address") ? $targetbasedata->setIsAddressChange(1) : $targetbasedata->setIsAddressChange(0);
        $targetbasedata->setOrderId($orderid);
        if (!is_null($status)) {
            $targetbasedata->setStatus($status);
        } else {
            $targetbasedata->setStatus("pending");
        }
        $targetbasedata->save();
    }

    /**
     * @param OrderItemInterface $orderItem
     */
    public function insertOrderItemData($orderItem, $qty)
    {
        $targetBaseOrder = $this->prepareOrderItemData($orderItem, $qty);
        $targetBaseOrder->save();
        return true;
    }

    /**
     * @param OrderItemInterface $orderItem
     */
    public function prepareOrderItemData($orderItem, $qty)
    {
        $order = $orderItem->getOrder();
        $product = $orderItem->getProduct();
        $payment = $order->getPayment();
        $methodTitle = ($payment->getMethod() =='aw_sarp_braintree_recurring')? "Credit Card"
            :$order->getPayment()->getAdditionalInformation('method_title');
        $orderType = $this->getOrderType($order->getId());

        /** @var TargetbaseOrder $targetBaseOrder */
        $targetBaseOrder = $this->targetbaseOrderFactory->create();
        $targetBaseOrder->setOrderId($order->getIncrementId());
        $targetBaseOrder->setStoreId($order->getStoreId());
        $targetBaseOrder->setPaymentMethod($methodTitle);
        $targetBaseOrder->setGrandTotal($order->getGrandTotal());
        $targetBaseOrder->setProductSku($orderItem->getSku());
        $targetBaseOrder->setProductPrice($orderItem->getPrice());
        $targetBaseOrder->setCreatedAt($order->getCreatedAt());
        $targetBaseOrder->setProductName($orderItem->getName());
        $targetBaseOrder->setProductBrand($product->getBrand());
        $targetBaseOrder->setProductQtyOrdered($qty);
        $targetBaseOrder->setOrderType($orderType);
        $targetBaseOrder->setCustomerId($order->getCustomerId());
        $targetBaseOrder->setCouponCode($order->getCouponCode());
        $targetBaseOrder->setTaxAmount($order->getTaxAmount());
        $targetBaseOrder->setShippingAmount($order->getShippingAmount());
        return $targetBaseOrder;
    }

    protected function getOrderType($orderId)
    {
        $sns = $this->orderManagementHelper->checkIsProgressiveAndBuyersRemorse($orderId);

        if (!empty($sns) && isset($sns['is_sns'])) {
            $orderType = 1; // 10% discount
            if (isset($sns['is_progressive']) && $sns['is_progressive']  == 1) {
                $orderType = 2; // for progressive discount product
            }
        } else {
            $orderType = 0; // one time purchase
        }
        return $orderType;
    }
}
