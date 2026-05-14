<?php

namespace Abbott\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface as AwSaprd2TokenIterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Model\DateTime\FormatConverter;
use Aheadworks\Sarp2\Model\Payment\Token;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Setup\Exception;
use Magento\Vault\Api\PaymentTokenRepositoryInterface as PaymentTokenRepositoryInterface;
use Psr\Log\LoggerInterface;
use Abbott\Sarp2\Helper\Data;
use Abbott\Sarp2\Helper\ChangeSubscription;

class Update extends Action
{
    public $profileAddressFactory;
    public $awSarp2Token;
    public $jsonHelper;
    public $authorization;
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ProfileInterfaceFactory
     */
    private $profileFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    private $resource;

    public $addressFactory;

    public $paymenttokenmanagement;

    public $paymentTokenFactory;

    public $paymentTokenInterfaceFactory;

    public $paymentsList;

    private $logger;

    private $helper;

    private $updateSubscribe;


    private $_subscription_history_helper;

    private $profileManagement;

    private $localeDate;

    private $dateFormatConverter;



    /**
     * @var
     */
    private $old_value;

    /**
     * @var
     */
    private  $new_value;

    CONST MBO_CARD_CHANGE = 'MBO_Card_change';

    CONST MBO_UPDATE = 'MBO_update';

    CONST SHIPPING_ADDRESS_TYPE = 'shipping';

    CONST BILLING_ADDRESS_TYPE = 'billing';

    /**
     * @var \Aheadworks\Sarp2\Model\Profile\Item
     */
    protected $_profile_item;

    /**
     * @var \Aheadworks\Sarp2\Model\Profile\Address
     */
    protected $_profile_address;

    /**
     * Update constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileInterfaceFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param ProfileAddressInterface $profileAddressFactory
     * @param PaymentTokenRepositoryInterface $paymenttokenmanagement
     * @param PaymentTokenInterface $paymentTokenFactory
     * @param PaymentTokenInterfaceFactory $paymentTokenInterfaceFactory
     * @param AwSaprd2TokenIterface $awSarp2Token
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param PaymentsList $paymentsList
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param ChangeSubscription $updateSubscribe
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Abbott\Subscriptionhistory\Helper\Data $subscriptionHistory
     * @param \Aheadworks\Sarp2\Model\Profile\ItemFactory $profile_item
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProfileInterfaceFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        ProfileAddressInterface $profileAddressFactory,
        PaymentTokenRepositoryInterface $paymenttokenmanagement,
        PaymentTokenInterface $paymentTokenFactory,
        PaymentTokenInterfaceFactory $paymentTokenInterfaceFactory,
        AwSaprd2TokenIterface $awSarp2Token,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        PaymentsList $paymentsList,
        ResourceConnection $resource,
        LoggerInterface $logger,
        Data $helper,
        ChangeSubscription $updateSubscribe,
        \Abbott\Subscriptionhistory\Helper\Data $subscriptionHistory,
        \Aheadworks\Sarp2\Model\Profile\ItemFactory $profile_item,
        ProfileManagementInterface $profileManagement,
        TimezoneInterface $localeDate,
        FormatConverter $dateFormatConverter,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->profileFactory = $profileFactory;
        $this->profileRepository = $profileRepository;
        $this->addressFactory = $addressFactory;
        $this->profileAddressFactory = $profileAddressFactory;
        $this->paymenttokenmanagement = $paymenttokenmanagement;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenInterfaceFactory = $paymentTokenInterfaceFactory;
        $this->awSarp2Token = $awSarp2Token;
        $this->jsonHelper = $jsonHelper;
        $this->paymentsList = $paymentsList;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->updateSubscribe = $updateSubscribe;
        $this->profileManagement = $profileManagement;
        $this->localeDate = $localeDate;
        $this->dateFormatConverter = $dateFormatConverter;
        $this->authorization = $authorization;
        $this->_subscription_history_helper = $subscriptionHistory;
        $this->_profile_item = $profile_item;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $session_name = $this->_subscription_history_helper->prepareMBOSessionName();
        $postData = $this->getRequest()->getParams();

        $newNextPaymentDate = ($postData['next-occ-date']) ? $postData['next-occ-date'] : null;
        $profileId = isset($postData['profile_id']) ? $postData['profile_id'] : null;
        $qtyUpdates = $postData['changed_qty'];


        if ($profileId) {
            try {
                $profile = $this->profileRepository->get($profileId);

                if (isset($postData['credit_card_info'])) {
                    $paymentTokenId = $this->updateCreditCardDetails($postData,$profile,$session_name['MBO_Card_change']);
                }

                $this->updateProfileItemQty($qtyUpdates, $session_name['MBO_product_qty_change'], $profile);

                $profile->setItemsQty(array_sum($qtyUpdates));
                if ($paymentTokenId) {
                    $profile->setPaymentTokenId($paymentTokenId);
                }

                /**
                 * Update Delivery Instruction
                 * ANAPOLLO-2738
                 */
                if (isset($postData['delivery_instruction'])) {
                    $profile->setDeliveryInstruction($postData['delivery_instruction']);
                }

                $profile->save();
                $this->updateProfileShippingAddress($postData,$session_name['MBO_shipping_address_change'], $profile);
                $this->updateProfileBillingAddress($postData, $session_name['MBO_billing_address_change'], $profile);

                if ($newNextPaymentDate) {

                    $payments = $this->paymentsList->getLastScheduled($profileId);
                    $existingPaymentDate = [];
                    foreach ($payments as $payment) {
                        /**
                         * Store existing Payment Schedule Date in session for Subsciption History Log
                         * Jira - ANSIMILAC-5145
                         */
                        if($this->_subscription_history_helper->getSubscriptionHistoryStatus($profile->getStoreId())){

                            $this->_subscription_history_helper->getOccuranceDateBeforeValue($session_name['MBO_occurance_date_change'],$payment);
                        }

                        $payment->setScheduledAt($newNextPaymentDate);
                        $payment->save();
                    }

                    $newNextPaymentDate = \DateTime::createFromFormat(
                        $this->dateFormatConverter->convertToDateTimeFormat(),
                        $newNextPaymentDate,
                        new \DateTimeZone($this->localeDate->getConfigTimezone())
                    );
                    $newNextPaymentDate = $this->localeDate->date($newNextPaymentDate, null, false);
                    $newNextPaymentDate = $newNextPaymentDate->format(DateTime::DATETIME_PHP_FORMAT);
                    $this->profileManagement->changeNextPaymentDate($profileId, $newNextPaymentDate);

                }

                if ($this->helper->getUpdateMailEnabled()) {
                    $this->updateSubscribe->updateSubscriptionNotificationAdminhtml();
                }

                /**
                 *  Save Newly modified data into $newData array
                 */
                $newData = [];
                if($this->_subscription_history_helper->getSubscriptionHistoryStatus($profile->getStoreId())) {
                    if ($paymentTokenId) {

                        /**
                         *  Compare New Card Details changed from MBO for Subscription History Log
                         * Jira ANSIMILAC-5145
                         */
                        $newData['MBO_Card_change'] = $this->_subscription_history_helper->compareCC($session_name['MBO_Card_change'],
                            $profile,
                            $this->new_value);

                    }


                    if (array_key_exists('MBO_Card_change',$newData) && ($newData['MBO_Card_change'] == NULL) || empty($newData)) {
                        unset($session_name['MBO_Card_change']);
                        unset($newData['MBO_Card_change']);
                    }


                    /**
                     * Compare Qty Changed of Subscription Profile Product for Subscription History Log from MBO
                     */
                    $newData['MBO_product_qty_change'] = $this->_subscription_history_helper->compareProductQty($session_name['MBO_product_qty_change'],
                        $profile,
                        $this->new_value,
                        $this->_profile_item
                    );

                    if (array_key_exists('MBO_product_qty_change',$newData) && $newData['MBO_product_qty_change'] == NULL) {
                        unset($session_name['MBO_product_qty_change']);
                        unset($newData['MBO_product_qty_change']);
                    }

                    $address_type = [self::SHIPPING_ADDRESS_TYPE, self::BILLING_ADDRESS_TYPE];
                    $addressSessionNames = [
                        self::SHIPPING_ADDRESS_TYPE => $session_name['MBO_shipping_address_change'],
                        self::BILLING_ADDRESS_TYPE => $session_name['MBO_billing_address_change']
                    ];

                    /**
                     * Compare Profile Billing and Shipping address for Subscription History Log from MBO
                     * Jira ANSIMILAC-5145
                     */
                    $newData = $this->_subscription_history_helper->compareProfileAddress($profile, $this->profileAddressFactory, $address_type, $addressSessionNames, $newData);

                    if (array_key_exists('MBO_shipping_address_change',$newData) && $newData['MBO_shipping_address_change'] == Null) {
                        unset($session_name['MBO_shipping_address_change']);
                        unset($newData['MBO_shipping_address_change']);
                    }
                    if (array_key_exists('MBO_billing_address_change',$newData) && $newData['MBO_billing_address_change'] == Null) {
                        unset($session_name['MBO_billing_address_change']);
                        unset($newData['MBO_billing_address_change']);
                    }

                    $newData['MBO_occurance_date_change'] = $this->_subscription_history_helper->compareNextOccuranceDate($session_name['MBO_occurance_date_change'], $profile);

                    if (array_key_exists('MBO_occurance_date_change',$newData) && $newData['MBO_occurance_date_change'] == Null) {
                        unset($session_name['MBO_occurance_date_change']);
                        unset($newData['MBO_occurance_date_change']);
                    }

                   $logs = $this->_subscription_history_helper->saveSubscriptionHistoryLog($profile, self::MBO_UPDATE, $session_name, $newData);
                }

                if($this->getRequest()->getParam('integration_test')){
                    return $logs;
                }
                $this->messageManager->addSuccessMessage(__("Subscription profile successfully updated."));

            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving profile.')
                );
            } catch (LocalizedException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    $exception->getMessage()
                );
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/edit', ["profile_id" => $profileId]);
    }

    public function updateCreditCardDetails($postData,$profile,$sessionName)
    {
        try{
            if ($postData['credit_card_info'] > 0) {
                $cardData = $this->paymenttokenmanagement->getById($postData['credit_card_info']);

                /**
                 *  Save existing card details in session
                 *  Jira ANSIMILAC-5145
                 */
                if($this->_subscription_history_helper->getSubscriptionHistoryStatus($profile->getStoreId()) && $cardData){
                    $this->_subscription_history_helper->getCreditCardDetailsBeforeSave($profile->getPaymentTokenId(),$sessionName);
                }

                $cardDetails = $this->jsonHelper->jsonDecode($cardData->getDetails());
                $prfileTokenCollection = $this->paymentTokenFactory->getCollection()->addFieldToFilter('token_value', ["eq" => $cardData['gateway_token']]);
                if ($cardData) {
                    $paymentToken = $this->paymentTokenInterfaceFactory->create();
                    $paymentToken->setPaymentMethod($cardData->getPaymentMethodCode())
                        ->setType($cardData->getType())
                        ->setTokenValue($cardData->getGatewayToken())
                        ->setExpiresAt($cardData->getExpiresAt())
                        ->setIsActive(true);
                    if ($cardData->getType() == Token::TOKEN_TYPE_CARD) {
                        $paymentToken->setExpiresAt($cardData->getExpiresAt());
                        $paymentToken->setDetails('type', $cardDetails['type']);
                        $paymentToken->setDetails('maskedCC', $cardDetails['maskedCC']);
                        $paymentToken->setDetails('expirationDate', $cardDetails['expirationDate']);
                    }

                    if($cardData->getType() == Token::TOKEN_TYPE_ACCOUNT){
                        $paymentToken->setDetails('payerEmail', $cardDetails['payerEmail']);
                    }

                    $this->awSarp2Token->save($paymentToken);

                    return $paymentToken->getTokenId();
                }
            }
        } catch (\Exception $e){
            $this->logger->critical($e->getMessage());
            return false;
        }

        return false;
    }

    public function updateProfileShippingAddress($postData, $sessionName, $profile)
    {
        $order_shipping_address_id = null;
        $subscription_shipping_id = null;
        if ($postData['shipping_address_id']) {
            $order_shipping_address_id = explode("__", $postData['shipping_address_id'])[1];
            $subscription_shipping_id = explode("__", $postData['shipping_address_id'])[0];
        }
        try {
            if ($order_shipping_address_id && $subscription_shipping_id) {
                $shippingAddress = $this->addressFactory->create()->load($order_shipping_address_id);
                $profileAddressData = $this->profileAddressFactory->load($subscription_shipping_id);

                /**
                 *  Save existing value of Profile shipping address in session for Subscription History Log
                 * Jira ANSIMILAC-5145
                 */
                if($this->_subscription_history_helper->getSubscriptionHistoryStatus($profile->getStoreId())){
                    $this->_subscription_history_helper->getProfileShippingAddressBeforeValue($profileAddressData, $sessionName);
                }


                $profileAddressData->setFirstname($shippingAddress->getFirstname());
                $profileAddressData->setLastname($shippingAddress->getLastname());
                $profileAddressData->setStreet($shippingAddress->getStreet()[0]);
                $profileAddressData->setCity($shippingAddress->getCity());
                $profileAddressData->setRegion($shippingAddress->getRegion());
                $profileAddressData->setRegionId($shippingAddress->getRegionId());
                $profileAddressData->setPostcode($shippingAddress->getPostcode());
                $profileAddressData->setCustomerAddressId($order_shipping_address_id);
                $profileAddressData->save();
            }
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while saving address.')
            );
        }
    }

    public function updateProfileBillingAddress($postData, $sessionName, $profile)
    {
        $order_billing_address_id = null;
        $subscription_billing_id = null;
        if ($postData['billing_address_id']) {
            $order_billing_address_id = explode("__", $postData['billing_address_id'])[1];
            $subscription_billing_id = explode("__", $postData['billing_address_id'])[0];
        }
        try {
            if ($order_billing_address_id && $subscription_billing_id) {
                $shippingAddress = $this->addressFactory->create()->load($order_billing_address_id);
                $profileAddressData = $this->profileAddressFactory->load($subscription_billing_id);

                /**
                 *  Save existing value of Profile billing address in session for Subscription History Log
                 * Jira ANSIMILAC-5145
                 */
                if($this->_subscription_history_helper->getSubscriptionHistoryStatus($profile->getStoreId())) {

                    $this->_subscription_history_helper
                        ->getProfileShippingAddressBeforeValue($profileAddressData, $sessionName);
                }

                $profileAddressData->setFirstname($shippingAddress->getFirstname());
                $profileAddressData->setLastname($shippingAddress->getLastname());
                $profileAddressData->setStreet($shippingAddress->getStreet()[0]);
                $profileAddressData->setCity($shippingAddress->getCity());
                $profileAddressData->setRegion($shippingAddress->getRegion());
                $profileAddressData->setRegionId($shippingAddress->getRegionId());
                $profileAddressData->setPostcode($shippingAddress->getPostcode());
                $profileAddressData->setCustomerAddressId($order_billing_address_id);
                $profileAddressData->save();
            }
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while saving address.')
            );
        }
    }

    private function updateProfileItemQty($itemsArray, $sessionName, $profile)
    {
        if (!empty($itemsArray)) {
            $connection = $this->resource->getConnection();

            /**
             *  Save existing value of products qty in session for Subscription History Log
             * Jira ANSIMILAC-5145
             */
            if($this->_subscription_history_helper->getSubscriptionHistoryStatus($profile->getStoreId())){
                $this->_subscription_history_helper->getProductQtyBeforeSave($sessionName, $profile, $this->_profile_item);
            }

            foreach ($itemsArray as $key => $value) {
                try {

                    $table = $connection->getTableName('aw_sarp2_profile_item');
                    $connection->update(
                        $table,
                        ['qty' => $value],
                        ['item_id IN(?)' => $key]
                    );

                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }
    }
    /**
     * Added resource for access
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
