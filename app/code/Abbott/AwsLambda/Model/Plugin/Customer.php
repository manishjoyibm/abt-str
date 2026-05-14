<?php
namespace Abbott\AwsLambda\Model\Plugin;

use Abbott\AwsLambda\Logger\Log;
use Abbott\AwsLambda\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;

class Customer
{
    const LOG_MESSAGE = 'Aws-Lambda-Api - profile info :';

    /**
     * @var Abbott\AwsLambda\Helper\Data
     */
    protected $helper;

    /**
     * @var Abbott\AwsLambda\Logger\Log
     */
    protected $log;

    /**
     * @var Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param \Abbott\AwsLambda\Helper\Data $helper
     * @param \Abbott\AwsLambda\Logger\Log  $log
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Data $helper,
        Log $log,
        ResultFactory $resultFactory,
        ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->log = $log;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    public  function aroundExecute(
        \Magento\Customer\Controller\Adminhtml\Index\Save $subject,
        $proceed
    ) {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $subject->getRequest()->getPostValue();
        $alternateEmail = "";

        if (isset($data['customer']) || $data['customer'] != '') {
            try {
                $this->helper->setStoreId($data['customer']['website_id']);
                $isNew = false;
                if ($subject->getRequest()->getParam('is_new')) {
                    $isNew = $subject->getRequest()->getParam('is_new');
                }
                if ($this->helper->enabled() && !$isNew) {

                    $this->log->writeLog('Aws-Lambda-Api - Profile Update By Admin : Started');
                    if (isset($data['customer']['alternate_email'])) {
                        $alternateEmail = $data['customer']['alternate_email'];
                    }
                    $params = '{
                    "userInfo": {
                        "firstName": "'.$data['customer']['firstname'].'",
                        "lastName": "'.$data['customer']['lastname'].'",
                        "contactEmail": "'.$alternateEmail.'"
                    },
                    "category": "profileInfo"
                }';
                    $url = $this->helper->getPostUrl();

                    $this->log->writeLog(
                        self::LOG_MESSAGE. print_r(
                            [
                                "Url" => $url,
                                "Params" => $params,
                                "log_time"=>date('d-m-Y H:i:s')
                            ],
                            true
                        )
                    );

                    $gigyaUid = $this->helper->getGigyaUid($data['customer']['entity_id']);
                    $response = $this->helper->postData($url, $params, $gigyaUid);

                    $this->log->writeLog(self::LOG_MESSAGE.  print_r(
                        [
                            "Response" => $response,
                            "log_time"=>date('d-m-Y H:i:s')
                        ],
                        true
                    ));

                    if ($response != null) {
                        $decodeResponse = json_decode($response, true);
                        if ($decodeResponse['errorCode'] != '0') {
                            $this->messageManager->addError($decodeResponse['response']['statusReason']);
                            return $resultRedirect->setRefererOrBaseUrl();
                        }
                    } else {
                        $this->messageManager->addError("Empty API response from Gigya");
                        return $resultRedirect->setRefererOrBaseUrl();
                    }
                }
                return $proceed();
            } catch (\Exception $e) {
                $this->log->writeLog(self::LOG_MESSAGE.  print_r(["Error" => $e->getMessage()], true));
            }
        }
    }
}
