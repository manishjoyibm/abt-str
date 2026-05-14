<?php

namespace Abbott\Checkout\Controller\Customer;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
use Magento\Customer\Controller\AbstractAccount;
use Abbott\AwsLambda\Logger\Log;
use Abbott\AwsLambda\Helper\Data;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Abbott\MyAccount\Model\MergeCart;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Abbott\Checkout\Helper\Data as CheckoutHelper;

class Create extends AbstractAccount
{
    public $formFactory;
    public $regionDataFactory;
    public $addressDataFactory;
    public $dataObjectHelper;
    public $checkoutHelper;
    public $request;
    public $customerUrl;
    const LOG_MESSAGE = 'Aws-Lambda-GetProfileApi : ';
    const DATE_FORMAT = 'd-m-Y H:i:s';
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepo;

    /**
     * @var \Abbott\AwsLambda\Helper\Data
     */
    protected $awsHelper;

    /**
     * @var \Abbott\AwsLambda\Logger\Log
     */
    protected $log;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AccountHelper
     */
    protected $accountHelper;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManagerInterface;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MergeCart
     */
    protected $mergeCartModel;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @param Context $context
     * @param CookieManagerInterface $cookieManagerInterface
     * @param CustomerExtractor $customerExtractor
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param RegionFactory $regionFactory
     * @param AccountHelper $accountHelper
     * @param StoreManagerInterface $storeManager
     * @param MergeCart $mergeCart
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param FormFactory $formFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Data $helper
     * @param Log $log
     * @param ManagerInterface $messageManager
     * @param PageFactory $resultPageFactory
     * @param Escaper $escaper
     * @param CustomerRepositoryInterface $customerInterface
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManagerInterface,
        CustomerExtractor $customerExtractor,
        Session $customerSession,
        AccountManagementInterface $accountManagement,
        RegionFactory $regionFactory,
        AccountHelper $accountHelper,
        StoreManagerInterface $storeManager,
        MergeCart $mergeCart,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        FormFactory $formFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        DataObjectHelper $dataObjectHelper,
        Data $helper,
        Log $log,
        ManagerInterface $messageManager,
        PageFactory $resultPageFactory,
        Escaper $escaper,
        CustomerRepositoryInterface $customerInterface,
        CheckoutHelper $checkoutHelper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->cookieManagerInterface = $cookieManagerInterface;
        $this->customerExtractor = $customerExtractor;
        $this->accountManagement = $accountManagement;
        $this->accountHelper = $accountHelper;
        $this->storeManager = $storeManager;
        $this->mergeCartModel = $mergeCart;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->formFactory = $formFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->awsHelper = $helper;
        $this->log = $log;
        $this->messageManager = $messageManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->regionFactory = $regionFactory;
        $this->escaper = $escaper;
        $this->customerRepo = $customerInterface;
        $this->checkoutHelper = $checkoutHelper;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->customerSession->isLoggedIn()) {
            return $resultRedirect->setPath('*/*/');
        }
        $this->initRequestContext();
        if (!$this->awsHelper->isCreateCustomerEnabled()) {
            return $this->customerSession->authenticate();
        }
        $token = $this->cookieManagerInterface->getCookie('x-id-token');
        $guestCartKey = $this->cookieManagerInterface->getCookie('abt_cartKey');
        if (!$token) {
            $this->accountHelper->setCustomcookie();
            return $this->customerSession->authenticate();
        }
        [$error, $message, $validationMsg] = $this->processCustomerCreation($token, $guestCartKey);
        if ($error) {
            return $this->handleCustomerCreationError($validationMsg, $message);
        }
        return $resultRedirect->setPath($this->getRedirectUrl());
    }

    public function createCustomer($guestCartKey)
    {
        try {
            try {
                $customer = $this->customerRepo->get(
                    $this->request->getParam('email'),
                    $this->storeManager->getStore()->getWebsiteId()
                );
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exp) {
                $customer = false;
            } catch (\Magento\Framework\Exception\LocalizedException $exp) {
                $customer = false;
            }

            if (!$customer) {
                $customer = $this->customerExtractor->extract('customer_account_create', $this->request);
                $address = $this->extractAddress();

                $addresses = $address === null ? [] : [$address];
                $customer->setAddresses($addresses);

                $extensionAttributes = $customer->getExtensionAttributes();
                $extensionAttributes->setIsSubscribed($this->getRequest()->getParam('is_subscribed', false));
                $customer->setExtensionAttributes($extensionAttributes);

                $password = $this->request->getParam('password');
                $redirectUrl = $this->customerSession->getBeforeAuthUrl();

                $customer = $this->accountManagement
                    ->createAccount($customer, $password, $redirectUrl);

                $this->_eventManager->dispatch(
                    'customer_register_success',
                    ['account_controller' => $this, 'customer' => $customer]
                );

                $this->log->writeLog('createCustomerAfter : ' .  print_r([
                    "cust_id" => $customer->getId(),
                        "email" => $customer->getEmail(),
                        "log_time"=>date(self::DATE_FORMAT)
                    ], true));

                $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());

                if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                    $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                    // @codingStandardsIgnoreStart
                    $this->addError(
                        __(
                            'You must confirm your account.'.
                            ' Please check your email for the confirmation link'.
                            ' or <a href="%1">click here</a> for a new link.',
                            $email
                        )
                    );
                    // @codingStandardsIgnoreEnd
                    return false;
                }
            }
            $cartId = $this->accountHelper->setAllStoreCookies($customer);
            $this->mergeCartModel->mergeCarts($customer, $guestCartKey, $cartId, false);
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();
            return $customer;
        } catch (StateException $e) {
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address.'
            );
            // @codingStandardsIgnoreEnd
            $this->addError($message);
        } catch (InputException $e) {
            $this->addError($this->escaper->escapeHtml($e->getMessage()));
            foreach ($e->getErrors() as $error) {
                $this->addError($this->escaper->escapeHtml($error->getMessage()));
            }
        } catch (Exception $e) {
            $message = __('We can\'t save the customer. ') . $e->getMessage();
            $this->addError($message);
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function addError($message)
    {
        throw new Exception($message);
    }

    /**
     * @param string $token
     * @return Exception|true
     * @throws Exception
     */
    public function setUserInfo($token)
    {
        $this->log->writeLog(
            self::LOG_MESSAGE .  print_r(["Request" => $token,"log_time"=>date(self::DATE_FORMAT)], true)
        );

        $response = json_decode($this->awsHelper->getProfile($token, []), true);

        $this->log->writeLog(
            self::LOG_MESSAGE .  print_r(["Response" => $response,"log_time"=>date(self::DATE_FORMAT)], true)
        );

        if (!empty($response['status']) && $response['status'] === true) {
            $user = $response['response']['userInfo'];

            /** validate user info **/
            $vresult = $this->validateCuctomerInfo($user);
            $this->log->writeLog(
                self::LOG_MESSAGE .  print_r([
                    "User Info Validation" => $vresult,
                    "log_time"=>date(self::DATE_FORMAT)
                ], true)
            );
            if ($vresult['error'] == 1) {
                return false;
            }

            $this->request->setParam('email', $user['userName']);
            $this->request->setParam('firstname', $user['firstName']);
            $this->request->setParam('lastname', $user['lastName']);
            $this->request->setParam('user_type', $user['userType']);
            $user['password'] = $this->getRandom();
            if (!empty($user['uid'])) {
                $this->request->setParam('gigya_uid', $user['uid']);
            }
            if (!empty($response['response']['addresses'][0])) {
                $address = $response['response']['addresses'][0];
                if (strlen($address['country']) != 2) {
                    $address['country'] = 'US';
                }
                $regionId = 0;
                $regionName = "";
                $addressLineTwo = !empty($address['lineTwo']) ? $address['lineTwo'] : '';
                if (!empty($address['state'])) {
                    $region = $this->regionFactory->create()->loadByCode($address['state'], $address['country']);
                    $regionId = $region->getRegionId();
                    $regionName = $region->getName();
                }

                $this->log->writeLog(
                    self::LOG_MESSAGE .  print_r([
                        "Response" => $user['userName'],
                        ["Address" => $address],
                        "log_time"=>date(self::DATE_FORMAT)
                    ], true)
                );

                $this->request->getPostValue('create_address', true);
                $this->request->setParam('region', $regionName);
                $this->request->setParam('region_id', $regionId);
                $this->request->setParam('street', [$address['lineOne']]);
                if (!empty($addressLineTwo)) {
                    $this->request->setParam('street', [$address['lineOne'], $addressLineTwo]);
                }
                $this->request->setParam('city', $address['city']);
                $this->request->setParam('country_id', $address['country']);
                $this->request->setParam('postcode', $address['zipCode']);
                $this->request->setParam('default_shipping', true);
                $this->request->setParam('default_billing', true);
            }
            $this->request->setParam('password', $user['password']);
            $this->request->setParam('password_confirmation', $user['password']);
            $this->request->setParam('is_new', true); //parameter to avoid extra sync to Gigya
            return true;
        }
        throw new Exception(
            "Aws-Lambda-GetProfileApi Error: ".
            (!empty($response['message'])) ? $response['message'] : "Error"
        );
    }

    protected function extractAddress()
    {
        $addressForm = $this->formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        $regionDataObject = $this->regionDataFactory->create();

        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->request->getParam($attributeCode);
            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $this->request->getParam('default_billing', false)
        )->setIsDefaultShipping(
            $this->request->getParam('default_shipping', false)
        );

        $addressDataObject->setIsDefaultBilling($this->request->getParam('default_billing', true));
        $addressDataObject->setIsDefaultShipping($this->request->getParam('default_shipping', true));

        return $addressDataObject;
    }

    /**
     * Returns random string
     *
     * @return string
     */
    private function getRandom()
    {
        $lowers = 'abcdefghjkmnpqrstuvwxyz';
        $uppers = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';
        $specials = '!$*-.=?@_';
        $len = 7;
        $chars = $lowers . $uppers . $digits . $specials;
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[random_int(0, $lc)];
        }
        $subStr = $lowers[random_int(1, 20)] .
            $uppers[random_int(1, 20)] .
            $digits[random_int(1, 6)] .
            $specials[random_int(1, 6)];
        return $str.$subStr;
    }

    /**
     * validate customer info
     * array
     * @return boolean
     */

    public function validateCuctomerInfo($user)
    {
        $isValid = ['error' => 0, 'msg' => ''];
        if (strlen($user['userName']) > 64 ||
            strlen($user['userName']) < 1 ||
            !preg_match('/^[a-z0-9\'\-\.\@\!\#\$\%\&\*\+\/\=\?\^\_\`\{\|\}\~\; ]+$/i', $user['userName'])
        ) {
            $isValid['error'] = 1;
            $isValid['msg'] = 'Email : Max – 64 characters, '.
                'Email Address must contain only letters, spaces, apostrophes, hyphens, @, and periods.';
            return $isValid;
        }

        if (strlen($user['firstName']) > 40 ||
            strlen($user['firstName']) < 1 ||
            !preg_match('/^[a-z\'\-\. ]+$/i', $user['firstName'])
        ) {
            $isValid['error'] = 1;
            $isValid['msg'] = 'First Name : Min – 1 | Max – 40 symbols,'.
                ' First Name must contain only letters, spaces, apostrophes, hyphens, and periods.';
            return $isValid;
        }
        if (strlen($user['lastName']) > 40 ||
            strlen($user['lastName']) < 1 ||
            !preg_match('/^[a-z\'\-\. ]+$/i', $user['lastName'])) {
            $isValid['error'] = 1;
            $isValid['msg'] = 'Last Name : Min – 1 | Max – 40 symbols,'.
                ' Last Name must contain only letters, spaces, apostrophes, hyphens, and periods.';
            return $isValid;
        }
        return $isValid;
    }

    /**
     * validate customer address
     * array
     * @return boolean
     */

    public function validateCustomerAddress($address)
    {
        $isValidAddress = ['error' => 0, 'msg' => ''];
        if (strlen($address['lineOne']) > 60 ||
            strlen($address['lineOne']) < 1 ||
            !preg_match('/^[a-z0-9\-\,\. ]+$/i', $address['lineOne'])
        ) {
            $isValidAddress['error'] = 1;
            $isValidAddress['msg'] = 'LineOne : Max 60 characters in each line,'.
                ' Mailing Address must contain letters, numbers, hash, hyphen, comma, period.';
            return $isValidAddress;
        }

        if (strlen($address['city']) > 40 ||
            strlen($address['city']) < 2 ||
            !preg_match('/^[a-z0-9\-\,\. ]+$/i', $address['city'])
        ) {
            $isValidAddress['error'] = 1;
            $isValidAddress['msg'] = 'City : Min – 2 | Max – 40 symbols,'.
                ' Mailing Address must contain letters, numbers, hash, hyphen, comma, period';
            return $isValidAddress;
        }

        if (strlen($address['state']) != 2) {
            $isValidAddress['error'] = 1;
            $isValidAddress['msg'] = 'State : 2 Chars abbreviation for state';
            return $isValidAddress;
        }

        if (strlen($address['zipCode']) != 5 || !preg_match('/^\d{5}$/i', $address['zipCode'])) {
            $isValidAddress['error'] = 1;
            $isValidAddress['msg'] = 'zipCode : 5 digits, must numbers';
            return $isValidAddress;
        }
        return $isValidAddress;
    }

    /**
     * Initialize request context including AWS helper and logger store scope.
     *
     * @return void
     */
    private function initRequestContext(): void
    {
        $this->request = $this->getRequest();
        $this->awsHelper->setStoreId($this->storeManager->getStore()->getId());
        $this->log->setScope($this->storeManager->getStore()->getId());
    }

    /**
     * Handle AWS profile retrieval, customer validation, and account creation logic.
     *
     * @param string $token
     * @param string|null $guestCartKey
     * @return array [int $error, string $message, string $validationMsg]
     */
    private function processCustomerCreation($token, $guestCartKey): array
    {
        $error = 0;
        $message = '';
        $validationMsg = '';
        try {
            $setUser = $this->setUserInfo($token);
            if ($setUser) {
                $customer = $this->createCustomer($guestCartKey);
                $this->log->writeLog('Aws-Lambda-GetProfileApi : Success' . $customer->getId());
            } else {
                $error = 1;
                $validationMsg = "There was an issue processing your request. 
                Please call our customer service team at 1-800-749-5596.";
            }
        } catch (Exception $e) {
            $error = 1;
            $message = $e->getMessage();
            $this->log->writeLog("ERROR - " . $message);
        }
        return [$error, $message, $validationMsg];
    }

    /**
     * Determine correct redirect URL after authentication attempt.
     *
     * @return string
     */
    private function getRedirectUrl(): string
    {
        $afterAuthUrl = $this->customerSession->getAfterAuthUrl() ?? '';
        return strlen($afterAuthUrl) > 10
            ? $afterAuthUrl
            : $this->url->getBaseUrl();
    }

    /**
     * Handle creation or validation errors by showing appropriate messages
     * and redirecting back to the cart or login screen.
     *
     * @param string $validationMsg
     * @param string $message
     * @return \Magento\Framework\App\ResponseInterface
     */
    private function handleCustomerCreationError(string $validationMsg, string $message)
    {
        $this->customerSession->setAfterAuthUrl("");
        $this->customerSession->setStopCreateCustomer(1);
        $redirectionUrl = $this->url->getUrl("/checkout/cart/");
        if (!empty($validationMsg)) {
            $this->messageManager->addErrorMessage($validationMsg);
        } else {
            $this->messageManager->addErrorMessage(
                "Oops! We have experienced a temporary issue. Please try again later"
            );
            if (!empty($message)) {
                $this->checkoutHelper->sendAdminNotification($message);
            }
        }
        return $this->customerSession->authenticate($redirectionUrl);
    }
}
