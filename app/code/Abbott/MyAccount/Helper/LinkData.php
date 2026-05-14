<?php

namespace Abbott\MyAccount\Helper;

use Abbott\AwsLambda\Logger\Log;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Abbott\MyAccount\Model\Config\Source\Action;
use Magento\Framework\HTTP\Client\Curl;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class LinkData extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    public $curl;
    public $log;
    public const XML_PATH_ACTIVE = 'my_account/my_account_general/active';

    public const XML_DISABLE_INLINE_EMAIL = 'my_account/customer_login/disable_inline_email';

    public const XML_FORGOT_RESET_PASSWORD_DISABLE = 'my_account/my_account_general/disable_password';

    public const XML_RESET_GIGYA_PASSWORD_API_URL = 'my_account/my_account_general/reset_gigya_password_api_url';

    public const XML_RESET_GIGYA_PASSWORD_AEM_APP_ID = 'my_account/my_account_general/aem_app_id';

    public const XML_SHARE_CUSTOMERS = 'customer/account_share/scope';

    public const XML_RESET_GIGYA_PASSWORD_API_URL_MBO =
        'my_account/my_account_general/mbo_reset_gigya_password_api_url';

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepo;

    protected $request;

    protected $awsHelper;

    /**
     * Construct function
     *
     * @param Context $context
     * @param \Abbott\AwsLambda\Helper\Data $awsHelper
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     * @param Log $log
     * @param CustomerRepositoryInterface $customerRepo
     */
    public function __construct(
        Context $context,
        \Abbott\AwsLambda\Helper\Data $awsHelper,
        StoreManagerInterface $storeManager,
        Curl $curl,
        Log $log,
        CustomerRepositoryInterface $customerRepo
    ) {
            $this->storeManager = $storeManager;
            $this->request = $context->getRequest();
            $this->customerRepo = $customerRepo;
            $this->curl = $curl;
            $this->log = $log;
            $this->awsHelper = $awsHelper;
            parent::__construct($context);
    }

    /**
     * Whether Tag Manager is ready to use
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * GetAction function
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->scopeConfig->getValue(
            'my_account/my_account_general/action',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * GetSectionList function
     *
     * @return array|string[]
     */
    public function getSectionList()
    {
        $list = $this->scopeConfig->getValue(
            'my_account/my_account_general/link_sections',
            ScopeInterface::SCOPE_STORE
        );
        return empty($list) ? [] : explode(',', $list);
    }

    /**
     * GetEmailEditDisableFlag function
     *
     * @param $storeId
     * @return mixed
     */
    public function getEmailEditDisableFlag($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_DISABLE_INLINE_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is link disable from backend then redirect to pagenotfound
     *
     * @param $layout
     * @return bool
     */
    public function checkForPagenotfound($layout)
    {
        if (Action::EXCLUDE_SELECTED == $this->getAction() && in_array($layout, $this->getSectionList())) {
            return true;
        }
        return false;
    }

    /**
     * Is forget password and reset password disabled
     *
     * @param $storeId
     * @return mixed
     */
    public function getIsPasswordDisable($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_FORGOT_RESET_PASSWORD_DISABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is forget password and reset password disabled
     *
     * @param $storeId
     * @return mixed
     */
    public function getGigyaResetPasswordUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_RESET_GIGYA_PASSWORD_API_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

/**
 * GetMboGigyaResetPasswordUrl function
 *
 * @param $storeId
 * @return mixed
 */
    public function getMboGigyaResetPasswordUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_RESET_GIGYA_PASSWORD_API_URL_MBO,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * GetAEMAppId function
     *
     * @param $storeId
     * @return mixed
     */
    public function getAEMAppId($storeId = null)
    {
            return $this->scopeConfig->getValue(
                self::XML_RESET_GIGYA_PASSWORD_AEM_APP_ID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    /**
     * GetResetPasswordCurlResponse function
     *
     * @param $customer
     * @return bool
     */
    public function getResetPasswordCurlResponse($customer)
    {
        $email = $customer->getEmail();
        $storeId = $customer->getStoreId();
        $this->log->writeLog('Inside Reset Password API funtion');
        $requesturl = $this->getMboGigyaResetPasswordUrl($storeId);
        $this->awsHelper->setStoreId($storeId);
        $aemAppId = $this->awsHelper->getAppId();
        $accessKey = $this->awsHelper->getAccessKey();
        $params = json_encode(['email' => $email]);
        try {
            $this->log->writeLog('AEM App Id '.$aemAppId);
            $this->log->writeLog('Request Params '.print_r(["RequestParams" => $params], true));
            if (!empty($aemAppId) && !empty($email)) {
                $this->curl->addHeader("Access-Control-Allow-Origin", "*");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("x-country-code", "US");
                $this->curl->addHeader("x-application-id", $aemAppId);
                $this->curl->addHeader("x-preferred-language", "en-US");
                $this->curl->addHeader("x-application-access-key", $accessKey);
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->curl->setOption(CURLOPT_ENCODING, "");
                $this->curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                $this->log->writeLog('Lambda Api - Reset Password request url : '.  $requesturl);
                $this->curl->post($requesturl, $params);
                $responseBody = $this->curl->getBody();
                $this->log->writeLog('Lambda Api - Reset Password : '.  print_r(["Response" => $responseBody], true));
                if (!empty($responseBody)) {
                    $res = json_decode($responseBody, true);
                    $this->log->writeLog(
                        'Lambda Api - Reset Password response json : '.  print_r(["ResponseJson" =>
                            $res], true)
                    );
                    if (!empty($res) && array_key_exists('status', $res) && $res['status'] == 1) {
                        return true;
                    }
                }
            } else {
                $this->log->writeLog('AEM app Id not set');
            }
        } catch (\Exception $ex) {
            $this->log->writeLog($ex->getMessage());
        }
        return false;
    }

    /**
     * GetCustomerStore function
     *
     * @return int|string|null
     */
    public function getCustomerStore()
    {
        $storeId = "";
        $customerId = $this->request->getParam('customer_id');
        if ($customerId) {
            try {
                $customer = $this->customerRepo->getById($customerId);
                $storeId = $customer->getStoreId();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exp) {
                $storeId = "";
            } catch (\Magento\Framework\Exception\LocalizedException $exp) {
                $storeId = "";
            }
        }
        return $storeId;
    }

    /**
     * IsCustomerShared function
     *
     * @return bool
     */
    public function isCustomerShared()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_SHARE_CUSTOMERS
        );
    }
}
