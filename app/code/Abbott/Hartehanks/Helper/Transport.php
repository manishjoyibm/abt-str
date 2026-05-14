<?php

namespace Abbott\Hartehanks\Helper;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

class Transport extends AbstractHelper
{

    public $curl;
    protected $encryptor;

    const XML_DAYS_SAVED = 'hartehanks/hartehanks/days_saved';

    const XML_TIME_LIMIT = 'hartehanks/hartehanks/time_limit';

    const XML_RETRY_TIME_LIMIT = 'hartehanks/hartehanks/retry_time_limit';

    const XML_RETRY_ONE_TIME_LIMIT = 'hartehanks/hartehanks/retry_time_limit_1';

    const XML_RETRY_TWO_TIME_LIMIT = 'hartehanks/hartehanks/retry_time_limit_2';

    const XML_RETRY_THREE_TIME_LIMIT = 'hartehanks/hartehanks/retry_time_limit_3';

    const XML_USER_NAME = 'hartehanks/hartehanks/username';

    const XML_ENCRYPTED = 'hartehanks/hartehanks/password';

    const XML_END_POINT = 'hartehanks/hartehanks/endpoint';

    const XML_ACCOUNT_CODE = 'hartehanks/hartehanks/account_code';

    const XML_IS_ENABLED = 'hartehanks/hartehank_email_template/is_enabled';

    const XML_TEST_ENABLED = 'hartehanks/hartehanks_placeorder/is_test_enabled';

    const XML_PLACEORDER_ID = 'hartehanks/hartehanks_placeorder/increment_id';

    const XML_PATH_INVENTORY_MANAGEMENT_ENABLE = 'hartehanks_inventory/general/enable';

    const XML_PATH_ORDER_STATUS = 'hartehanks_inventory/general/order_status';

    const XML_PATH_DAYS = 'hartehanks_inventory/general/days';

    const XML_PLACEORDER_DEFAULT_USERNAME = 'hartehanks/hartehanks_placeorder/default_username';

    const XML_PLACEORDER = 'hartehanks/hartehanks_placeorder/';

    const XML_FIND_ORDER_TIME_INTERVAL = 'hartehanks/hartehanks/time_limit_find_order';

    const XML_FIND_ORDER_DAYS_INTERVAL = 'hartehanks/hartehanks/days_limit_find_order';

    const XML_DEBUG = 'hartehanks/hartehanks/is_debug_enabled';

    const XML_ADVANCE_DEBUG = 'hartehanks/hartehanks/is_advance_debug_enabled';

    const XML_ORDER_COLLECTION_SIZE = 'hartehanks/hartehanks/order_collection_size';

    const XML_FINDORDER_COLLECTION_SIZE = 'hartehanks/hartehanks/findorder_collection_size';

    const XML_FINDORDER_TEST = 'hartehanks/hartehanks_findorder/findOrder_test';

    const XML_FINDORDER_ID = 'hartehanks/hartehanks_findorder/increment_id';

    const XML_FINDORDER_CRON_ENABLE = 'hartehanks/hh_cron/fo_cron_enable';

    const STATUS_PENDING = 'Pending';

    const STATUS_PROCESSED = 'Processed';

    const STATUS_FAILED = 'Failed';

    const MESSAGE_PENDING = 'No Records Yet Added';

    const FILE_CONTENT_TYPE = 'HarteHanks';

    const ORDER_STATUS = 'sent_to_warehouse';

    const ORDER_STATUS_PENDING_INVOICE = 'pending_invoice';

    const ORDER_STATUS_FAIL = 'error';

    const ORDER_ERROR = 'error';

    const FILE_NAME = 'HH Inventory Service';

    const ORDER_FILE_NAME = 'HH PlaceOrder Service';

    const STATUS_SUCCESS = 'success';

    const RECEIVER_NAME = 'ABBOTT';

    const PLACEORDER_IDENTIFIER = 'PlaceOrder';

    const FINDORDER_IDENTIFIER =  'FindOrder';

    const RECEIVER_EMAIL = 'hartehanks/hartehank_email_template/receiver_email';

    const XML_PATH_EMAIL_SENDER = 'hartehanks/hartehank_email_template/sender';

    const EMAIL_TEMPLATE = 'hartehanks/hartehank_email_template/email_template';

    const PLACEORDER_TEMPLATE = 'hartehanks/hartehank_email_template/hartehank_placeorder_template';

    const FAILURE_EMAIL_TEMPLATE = 'failure_email_template';

    const HARTHANK_FEED_TABLE = 'apollo_hartehank_log';

    const HARTHANK_PLACEORDER_TABLE = 'apollo_hartehank_placeorder_log';

    const SOAP_EXCEPTION = 'Exception';

    const SOAP_FI_SERVICE = 'Find-Items-Service';

    const ORDER_DETAIL_SERVICE = 'Order-Detail-Service';

    const SOAP_ATTRIBUTE = '_attribute';

    const SOAP_VALUE = '_value';

    const STATUS = 'status';

    const SOAP_PO_SERVICE = 'Place-Order-Service';

    const SOAP_FO_SERVICE = 'Find-Order-Service';

    const SOAP_WEB_SERVICE_STATUS = 'WebServiceStatus';

    const XML_PATH_TEST_ENABLE = 'hartehanks_inventory/general/enable_test';

    const SOAP_ERRORS = 'Errors';

    const SOAP_ORDERS = 'Orders';

    const ORDER_ID = 'OrderId';

    const REQUEST_TYPE = 'Request Type';

    const SOAP_ENVELOPE = 'soap:Envelope';

    const SOAP_BODY = 'soap:Body';

    const SOAP_VENDOR_ORDER_ID = 'VendorOrderId';

    const ORDER_STATUS_BACKORDERED = 'backordered';

    const ORDER_STATUS_BACKORDERED_LABEL = 'Backordered';

    const ORDER_STATUS_COMPLETE_LABEL = 'Complete';

    const ORDER_STATUS_CANCELLED_LABEL = 'Cancelled';

    const ORDER_STATUS_COMPLETE = 'complete';

    const SOAP_FAULT = 'soap:Fault';

    const SOAP_ORDER = 'Order';

    const ORDER_STATUS_PARTIALLY_SHIPPED = 'partially_shipped';

    const ORDERDETAIL_IDENTIFIER = 'OrderDetail';

    const XML_PATH_BRAINTREE_AUTHORIZATION_EXPIRED_MESSAGE = 'hartehanks/hartehanks/braintree_cancel_order_message';

    const ORDER_STATUS_CANCEL = 'canceled';

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;


    public function __construct(
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        LoggerInterface $logger,
        Config $config,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
    }

    public function getDays()
    {
        return $this->scopeConfig->getValue(self::XML_DAYS_SAVED);
    }

    public function getSyncMinutes()
    {
        return $this->scopeConfig->getValue(self::XML_TIME_LIMIT);
    }

    public function getRetryTimeLimit()
    {
        return $this->scopeConfig->getValue(self::XML_RETRY_TIME_LIMIT);
    }

    public function getUserName()
    {
        return $this->scopeConfig->getValue(self::XML_USER_NAME);
    }

    public function getTestStatus()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TEST_ENABLE);
    }

    public function getPassword()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XML_ENCRYPTED));
    }

    public function getEndPoint()
    {
        return $this->scopeConfig->getValue(self::XML_END_POINT);
    }

    public function getInventoryManagementEnable()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INVENTORY_MANAGEMENT_ENABLE);
    }

    public function getOrderStatus()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORDER_STATUS);
    }

    public function getOrderDays()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DAYS);
    }

    public function getAccountCode()
    {
        return $this->scopeConfig->getValue(self::XML_ACCOUNT_CODE);
    }

    public function getCurlResponse($string)
    {
        $xmlPostString = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.ntouch.hartehanks.com/">
                            <soapenv:Header/>
                              <soapenv:Body>
                                  <web:callXMLService>
                                      <userName>'.$this->getUserName().'</userName>
                                      <password>'.$this->getPassword().'</password>
                                      <accountCode>'.$this->getAccountCode().'</accountCode>
                                      '.$string.'
                                  </web:callXMLService>
                              </soapenv:Body>
                          </soapenv:Envelope>';
        try {
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_POSTFIELDS, $xmlPostString);
            $this->curl->post($this->getEndPoint(), null);
            return $this->curl->getBody();
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }
    }

    public function getFindItemQuery()
    {
        return '<functionIdentifier>FindItems</functionIdentifier>
                <xml><![CDATA[<?xml version="1.0" encoding="utf-8"?> <Filters ItemCode="*"
                ItemDescription="*" />]]></xml>';
    }

    public function getOrderXmlQuery($identifier, $xmlStr)
    {
        return  '<functionIdentifier>'.$identifier.'</functionIdentifier>
                 <xml><![CDATA[<?xml version="1.0" encoding="utf-8"?>'.$xmlStr.']]></xml>';
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_IS_ENABLED);
    }

    public function getToMails()
    {
        return explode(",", $this->scopeConfig->getValue(self::RECEIVER_EMAIL));
    }

    public function sendEmail($template, $templateData, $mail)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $template = $this->scopeConfig->getValue(
            $template,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $sender = $this->scopeConfig->getValue(
            Transport::XML_PATH_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
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
             Transport::RECEIVER_NAME
         )->getTransport();
        $transport->sendMessage();
    }

    public function testEnable()
    {
        return $this->scopeConfig->getValue(self::XML_TEST_ENABLED);
    }

    public function getOrderCollectionSize()
    {
        $size = (int)$this->scopeConfig->getValue(self::XML_ORDER_COLLECTION_SIZE);
        return ($size >= 20 || $size == 0) ? 10 : $size;
    }

    public function getFindOrderCollectionSize()
    {
        return (int)$this->scopeConfig->getValue(self::XML_FINDORDER_COLLECTION_SIZE);
    }

    public function getFindOrderTime()
    {
        return $this->scopeConfig->getValue(self::XML_FIND_ORDER_TIME_INTERVAL);
    }

    public function enableDebug()
    {
        return $this->scopeConfig->getValue(self::XML_DEBUG);
    }

    public function isAdvanceDebugEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_ADVANCE_DEBUG);
    }

    public function getFindOrderDays()
    {
        return $this->scopeConfig->getValue(self::XML_FIND_ORDER_DAYS_INTERVAL);
    }

    public function getFindOrderTest()
    {
        return $this->scopeConfig->getValue(self::XML_FINDORDER_TEST);
    }

    public function getFindOrderId()
    {
        return explode(',', $this->scopeConfig->getValue(self::XML_FINDORDER_ID));
    }

    public function getPlaceOrderId()
    {
        return explode(',', $this->scopeConfig->getValue(self::XML_PLACEORDER_ID));
    }

    public function getPlaceOrderDefaultUserName()
    {
        return $this->scopeConfig->getValue(self::XML_PLACEORDER_DEFAULT_USERNAME);
    }

    public function getPlaceOrderConfig($configName)
    {
        return $this->scopeConfig->getValue(self::XML_PLACEORDER.$configName);
    }

    public function isFindOrderCronEnable()
    {
        return $this->scopeConfig->getValue(self::XML_FINDORDER_CRON_ENABLE);
    }

    public function getRetryTimeLimitOne()
    {
        return $this->scopeConfig->getValue(self::XML_RETRY_ONE_TIME_LIMIT);
    }

    public function getRetryTimeLimitTwo()
    {
        return $this->scopeConfig->getValue(self::XML_RETRY_TWO_TIME_LIMIT);
    }

    public function getRetryTimeLimitThree()
    {
        return $this->scopeConfig->getValue(self::XML_RETRY_THREE_TIME_LIMIT);
    }

    public function getBraintreeAuthorizationExpiredMessage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BRAINTREE_AUTHORIZATION_EXPIRED_MESSAGE);
    }

    /**
     * @param $exception
     * @param int|string $requestType
     * @param int|string|null $orderId
     * @return void
     */
    public function sendNewRelicAlert($exception, int|string $requestType, int|string|null $orderId = "false")
    {

        if ($this->config->isNewRelicEnabled()) {

            $this->newRelicWrapper->reportError($exception);

            if ($requestType == self::PLACEORDER_IDENTIFIER || $requestType == self::ORDERDETAIL_IDENTIFIER) {
                $this->newRelicWrapper->addCustomParameter(self::ORDER_ID, $orderId);
            } elseif ($requestType == self::FINDORDER_IDENTIFIER) {
                $this->newRelicWrapper->addCustomParameter('Request', $orderId);
            }
            $this->newRelicWrapper->addCustomParameter(self::REQUEST_TYPE, $requestType);

        }
    }
}
