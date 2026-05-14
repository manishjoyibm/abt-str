<?php

namespace Abbott\Hartehanks\Controller\Adminhtml\HarteHank;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Abbott\Hartehanks\Helper\Transport;
use Magento\Framework\Xml\Parser;

class Validate extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    protected $parser;

    protected $jsonFactory;

    protected $transportHelper;

    public function __construct(
        Context $context,
        Transport $transportHelper,
        Parser $parser,
        JsonFactory $jsonFactory
    ) {
        $this->transportHelper = $transportHelper;
        $this->parser= $parser;
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $resultPage = $this->jsonFactory->create();
        $xmlPostString = '<functionIdentifier>PING</functionIdentifier>
                            <xml></xml>';
        $response = $this->transportHelper->getCurlResponse($xmlPostString);
        $cleanXml = str_ireplace('&lt;', '<', $response);
        $resultArray = $this->parser->loadXML($cleanXml)->xmlToArray();
        $result = $resultArray['soap:Envelope']['soap:Body']['ns2:callXMLServiceResponse']['return'];

        file_put_contents(
            BP . '/var/log/new_relic.log',
            print_r($resultArray,true) . PHP_EOL,
            FILE_APPEND
        );

        if (array_key_exists(Transport::SOAP_WEB_SERVICE_STATUS, $result)) {
            $result = $result[Transport::SOAP_WEB_SERVICE_STATUS];
        } elseif (array_key_exists(Transport::SOAP_EXCEPTION, $result)) {
            $result = $result[Transport::SOAP_EXCEPTION][Transport::SOAP_VALUE];
        }

        if (array_key_exists(Transport::SOAP_ERRORS, $result)) {
            $errors = $result[Transport::SOAP_ERRORS]['Error'];
            $errorsArray = [];
            if (array_key_exists('0', $errors)) {
                $errorsArray = $errors;
            } else {
                $errorsArray[0] = $errors;
            }
            foreach ($errorsArray as $error) {
                $status = $error[Transport::SOAP_VALUE];
                $this->transportHelper->sendNewRelicAlert(new \Exception($status), 'Hartehank PING Service', false);
            }
        } else {
            $status = $result[Transport::SOAP_ATTRIBUTE][Transport::STATUS];
        }
        return $resultPage->setData($status);
    }
}
