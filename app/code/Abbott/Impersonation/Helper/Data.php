<?php

namespace Abbott\Impersonation\Helper;

use Magento\Framework\HTTP\Client\Curl; 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;


class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    public $transportBuilder;
    public $log;
    /**
     *
     * @var curl
     */
    protected $curl;
    protected $dirList;
     /**
     * @var File
     */
    protected $file;

    /**
     *
     * @var awslambdahelper
     */
    protected $awslambdahelper;

    protected $encryptor;
    protected $customerRepository;

    public function __construct(
        Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        \Abbott\AwsLambda\Helper\Data $awslambdahelper,
        \Abbott\AwsLambda\Logger\Log $log,
        CustomerRepositoryInterface $customerRepository,
        DirectoryList $dirList,
        File $file
    ) {
        $this->curl = $curl;
        $this->transportBuilder = $transportBuilder;
        $this->curl = $curl;
        $this->awslambdahelper = $awslambdahelper;
        $this->log = $log;
        $this->customerRepository = $customerRepository;
        $this->dirList = $dirList;
        $this->file = $file;

    }
    public function getCurlResponse($requesturl, $customerId)
    {
        $this->log->writeLog('Inside Impersonation Lambda API funtion');
        $attruid = $this->getAttributeValue($customerId); 
        $params = '';
		$profileResponse = [];
        try {
            $this->log->writeLog('Access Key '.$this->awslambdahelper->getAccessKey());
            $this->log->writeLog('App Id '.$this->awslambdahelper->getAppId());
            $this->log->writeLog('Uid array : '.  print_r(["UIDARR" => $attruid], true));
            if (!empty($attruid) && !empty($this->awslambdahelper->getAccessKey())) {
				 
				$this->curl->addHeader("Access-Control-Allow-Origin","*");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("x-country-code", "US");
                $this->curl->addHeader("x-application-id", $this->awslambdahelper->getAppId());
                $this->curl->addHeader("x-preferred-language", "en-US");
				$this->curl->addHeader("x-origin-secret", $this->awslambdahelper->getApppOriginSecret());
                $this->curl->addHeader("x-application-access-key", $this->awslambdahelper->getAccessKey());
                $this->curl->addHeader("uid", $attruid);
             				
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
				$this->curl->setOption(CURLOPT_ENCODING, "");				
				$this->curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				$this->log->writeLog('Lambda Api - Impersonation request url : '.  $requesturl);
                $this->curl->post($requesturl, $params);
				$this->log->writeLog('Lambda Api - Impersonation get coockies full');
				//added @ for suppresing the warning from core funtion getCookiesFull
				$response = @$this->curl->getCookiesFull();
				$responseBody = $this->curl->getBody();
                $this->log->writeLog('Lambda Api - Impersonation : '.  print_r(["Response" => $responseBody], true));
				$this->log->writeLog('Lambda Api - Impersonation get full cookies : '.  print_r(["fullCookies" => $response], true));
				if(!empty($responseBody)){
					$res = json_decode($responseBody, true);
					if(!empty($res)){
						if(array_key_exists('userType', $res['response']['accountInfo']['data'])){
							$res['response']['accountInfo']['profile']['userType'] = $res['response']['accountInfo']['data']['userType'];
						}
						$profileResponse['profile'] = ['value' => json_encode($res['response']['accountInfo']['profile'])];
						$this->log->writeLog('Lambda Api - Impersonation  profile response : '.  print_r($profileResponse, true));
						$response = array_merge($response, $profileResponse);
						  $this->log->writeLog('Lambda Api - Impersonation get full cookies after merge: '.  print_r(["fullCookies" => $response], true));
					}
				}
				$this->log->writeLog('Lambda Api - Impersonation response cookies : '.  print_r($response, true)); 
                return $response;
            }
            else{
                $this->log->writeLog('Gigya Uid not set for customer'); 
                return false;
            }
        } catch (\Exception $ex) {
            $this->log->writeLog($ex->getMessage());
        }
    }
    public function getAttributeValue($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        if($customer->getCustomAttribute('gigya_uid')){
            return $customer->getCustomAttribute('gigya_uid')->getValue();
        }else{
            return false;
        }
    }
}
