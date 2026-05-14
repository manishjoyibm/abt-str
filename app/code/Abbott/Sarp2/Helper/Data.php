<?php
namespace Abbott\Sarp2\Helper;

use Amasty\Orderattr\Model\Config\Source\Boolean;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Api\OrderRepositoryInterface;
use \Psr\Log\LoggerInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $storeManager;
    public $planRepository;
    const EMAIL_CANCEL_TEMPLATE = 'subscription_email_setting/subscription_cancel_email/cancel_subscription_template';
    const EMAIL_UPDATE_TEMPLATE = 'subscription_email_setting/subscription_update_email/update_subscription_template';
    const EMAIL_SENDER = 'subscription_email_setting/subscription_cancel_email/sender';
    const EMAIL_CANCEL_ENABLED = 'subscription_email_setting/subscription_cancel_email/enabled';
    const EMAIL_UPDATE_ENABLED = 'subscription_email_setting/subscription_update_email/enabled';
    const STORE_URL = 'my_account/myAccount_redirect/redirect_failure_url';
    const STORE_PHONE = 'general/store_information/phone';
    const EMAIL_GENERAL_STORE_SETTINGS = "trans_email/ident_general/email";
    const NAME_GENERAL_STORE_SETTINGS = "trans_email/ident_general/name";
    const CAN_CHANGE_PRODUCT = "subscription_change_product_setting/subscription_cancel_email/can_change_product";
    const AEM_URL_PRODUCT_LISTING = "subscription_change_product_setting/subscription_cancel_email/aem_product_listing_path";
    const PLAN_LIST = "subscription_change_product_setting/subscription_cancel_email/plan";
    const IS_PROGRESSIVE = 1;
    const EMAIL_OUT_OF_STOCK_TEMPLATE = 'subscription_email_setting/subscription_out_of_stock_email/subscription_out_of_stock_template';
    const LIMIT_PAYMENT_CHANGE_PER_PROFILE = 'abbott_subscription/payment_change_limitations/limit_payment_change_per_profile';
    const TIME_LIMIT_PAYMENT_CHANGE_PER_PROFILE = 'abbott_subscription/payment_change_limitations/time_limit_payment_change_per_profile';
    const LIMIT_PAYMENT_CHANGE_PER_PROFILE_MESSAGE = 'abbott_subscription/payment_change_limitations/limit_payment_change_per_profile_message';
    const LIMIT_PAYMENT_CHANGE_INVALID = 'abbott_subscription/payment_change_limitations/limit_payment_change_invalid';
    const TIME_LIMIT_PAYMENT_CHANGE_INVALID = 'abbott_subscription/payment_change_limitations/time_limit_payment_change_invalid';
    const LIMIT_PAYMENT_CHANGE_INVALID_MESSAGE = 'abbott_subscription/payment_change_limitations/limit_payment_change_invalid_message';
    const TEST_UNSUCCESSFUL_TRANSACTION = 'abbott_subscription/payment_change_limitations/test_unsuccessful_transaction';
    const DO_TAG_VALUE = 'buy_now_tagging/buy_now/javascript';
    const CHECK_ENABLED = 'buy_now_tagging/buy_now/enabled';
    const SOURCE_URL = 'buy_now_tagging/buy_now/source_url';
    const CSR_USER_ROLE = 'CSR';
    const XML_PATH = 'annual_reminder_section/annual_reminder/';

    public $orderSender;

    public $orderRepository;

    public $logger;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $store = null;

    /**
     * @var Session
     */
    protected $adminSession;
     /**
     * @var TimezoneInterface
     */
    private $timezone;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        OrderSender $orderSender,
        OrderRepositoryInterface $orderRepository,
        PlanRepositoryInterface $planRepository,
        LoggerInterface $logger,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Auth\Session $adminSession,
        TimezoneInterface $timezone
    ) {
        $this->storeManager = $storeManager;
        $this->orderSender = $orderSender;
        $this->orderRepository = $orderRepository;
        $this->planRepository = $planRepository;
        $this->logger = $logger;
        $this->adminSession = $adminSession;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * @param $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStoreByStoreId($storeId) {
        $store = $this->storeManager->getStore($storeId);
        return $this->setStore($store);
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function setStore(\Magento\Store\Api\Data\StoreInterface $store) {
        $this->store = $store;
        return $this->store;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore() {
        if(!$this->store) {
            $store = $this->storeManager->getStore();
            return $this->setStore($store);
        }
        return $this->store;
    }

    public function getCancelTemplate()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CANCEL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getCancelMailEnabled()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CANCEL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getUpdateTemplate()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_UPDATE_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getUpdateMailEnabled()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_UPDATE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getSenderEmail()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getStoreUrl()
    {
        return $this->scopeConfig->getValue(
            self::STORE_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getStorePhone()
    {
        return $this->scopeConfig->getValue(
            self::STORE_PHONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getStoreUpdateTemplate($storeId)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_UPDATE_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getStoreSenderEmail($storeId)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_GENERAL_STORE_SETTINGS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getStoreSenderName($storeId)
    {
        return $this->scopeConfig->getValue(
            self::NAME_GENERAL_STORE_SETTINGS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

     /**
     * send order confirmation email
     *
     * @param  $payment
     * @return void
     */
    public function sendOrderConfirmationEmail($payment)
    {
        try {
            if ($payment) {
                $order = $this->orderRepository->get($payment->getOrderId());
                $this->orderSender->send($order);
            }
        } catch (\Exception $e) {
            $this->logger->error("Order-Confirmation-Email-Exception" . $e->getMessgae());
        }
    }

    /**
     *
     * @param type $storeId
     * @return type
     */
    public function getCanChangeProduct($storeId) {
        return $this->scopeConfig->getValue(
                        self::CAN_CHANGE_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     *
     * @param type $storeId
     * @return type
     */
    public function getAemProductListingUrl($storeId) {
        return $this->scopeConfig->getValue(
                        self::AEM_URL_PRODUCT_LISTING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     *
     * @param type $storeId
     * @return type
     */
    public function getPlanList($storeId) {
        return $this->scopeConfig->getValue(
                        self::PLAN_LIST, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     *
     * @param type $storeId
     * @param type $planId
     * @param type $profileId
     * @return boolean
     */
    public function getChangeProductUrl($storeId, $planId, $profileId, $sku){
        if($this->getCanChangeProduct($storeId)!= ''){
            $panList = explode(',', $this->getPlanList($storeId));
            if(in_array($planId, $panList)){
                return $aemPath = $this->getAemProductListingUrl($storeId).'?profile_id='.$profileId.'&old_sku='.$sku.'&rpath=aw_sarp2/profile_edit/index/profile_id/'.$profileId;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function getRemoveProductUrl($profileId, $sku){
        if($profileId && $sku){
            return $this->storeManager->getStore()->getUrl('awsarp2/profile/remove', [ 'profile_id' => $profileId, 'sku' => $sku]);
        }
        return false;
    }



    /**
     *
     * @param type $planId
     * @param type $itemCount
     * @return boolean
     */
    public function canRemoveProduct($planId, $itemCount) {
        if ($planId) {
            $planType = $this->planRepository->get($planId)->getIsProgressive();
            if($itemCount > 1 && $planType != self::IS_PROGRESSIVE){
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     *
     * @param type $planId
     * @param type $storeId
     * @return string
     */
    public function allowPlanEdit($planId, $storeId){
        if($planId && $storeId) {
            $storeCode = $this->storeManager->getStore($storeId)->getCode();
            if($storeCode == \Abbott\MyAccount\Helper\Data::NEW_SIM_STORE_CODE){
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function getOutOfStockUpdateTemplate()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_OUT_OF_STOCK_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getPaymentChangeLimitPerProfile()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_PAYMENT_CHANGE_PER_PROFILE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getPaymentChangeTimeLimitPerProfile()
    {
        return $this->scopeConfig->getValue(
            self::TIME_LIMIT_PAYMENT_CHANGE_PER_PROFILE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getPaymentChangeLimitPerProfileMessage()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_PAYMENT_CHANGE_PER_PROFILE_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getPaymentChangeLimitInvalid()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_PAYMENT_CHANGE_INVALID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getPaymentChangeTimeLimitInvalid()
    {
        return $this->scopeConfig->getValue(
            self::TIME_LIMIT_PAYMENT_CHANGE_INVALID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getPaymentChangeLimitInvalidMessage()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_PAYMENT_CHANGE_INVALID_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    public function getTestUnsuccessfulTransaction()
    {
        return $this->scopeConfig->getValue(
            self::TEST_UNSUCCESSFUL_TRANSACTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
      * Get the store Id
      * @return int
      */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get value of Do Tag for Buy Now Reconnect
     *
     * @return string
     */
    public function getDoTagValue()
    {
        return $this->scopeConfig->getValue(
                        self::DO_TAG_VALUE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()
        );
    }

    /**
     * Get value of source Url
     *
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->scopeConfig->getValue(
                        self::SOURCE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()
        );
    }

      /**
      * Get the Module Config
      * @return mixed
      */
    public function getModuleConfig()
    {
        return $this->scopeConfig->getValue(
            self::CHECK_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get the User Role
     * @return bool
     */
    public function isCsrUser()
    {
        $role = $this->adminSession->getUser()->getRole()->getData();
        if($role['role_name'] == self::CSR_USER_ROLE) {
            return true;
        }
            return false;
    }
    /**
     * Check whether the Annual Reminder feature is enabled for the given store.
     *
     * Reads: abbott_sarp2/annual_reminder/enabled
     *
     * @param int|null $storeId
     *     Optional store ID. If omitted, falls back to the helper's getStoreId() logic.
     *
     * @return bool
     *     TRUE if the feature is enabled for the store (SCOPE_STORE), FALSE otherwise.
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH . 'enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Returns the cron expression used to schedule the Annual Reminder cron job.
     *
     * Reads: abbott_sarp2/annual_reminder/cron_expression
     *
     * @param int|null $storeId
     *     Optional store ID. If omitted, falls back to the helper's getStoreId().
     *
     * @return string
     *     A valid CRON expression string (e.g., "0 6 * * *").
     */
    public function getCronExpression(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH . 'cron_expression',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Returns the email template ID for the Annual Subscription Reminder.
     *
     * Reads: abbott_sarp2/annual_reminder/annual_subscription_template
     *
     * Falls back to the template declared in email_templates.xml:
     *     abbott_sarp2_annual_reminder_template
     *
     * @param int|null $storeId
     *     Optional store ID. If omitted, falls back to the helper's getStoreId().
     *
     * @return string
     *     The template ID/code to be passed to TransportBuilder.
     */
    public function getTemplateId(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH . 'annual_subscription_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) ?: 'abbott_sarp2_annual_reminder_template';
    }

    /**
     * Returns how many days before the anniversary the reminder email should be sent.
     *
     * Reads: abbott_sarp2/annual_reminder/days_before
     *
     * Business rule:
     * - Values >= 0 are valid.
     * - A value of 0 means "send ON the anniversary date" (same‑day reminder).
     * - Negative values are not allowed and default to 5.
     *
     * @param int|null $storeId
     *     Optional store ID. If omitted, falls back to helper's getStoreId().
     *
     * @return int
     *     Number of days before the anniversary to send the reminder (0, 1, 2, ...)
     */
    public function getDaysBefore(?int $storeId = null): int
    {
        $v = (int)$this->scopeConfig->getValue(
            self::XML_PATH . 'days_before',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $v >= 0 ? $v : 5;
    }

    /**
     * Check whether Test Mode is enabled for Annual Reminder processing.
     *
     * When enabled, cron uses the configured "test_date_time" instead of the
     * actual store‑local "today" value. This allows testing date‑driven behavior.
     *
     * Reads: abbott_sarp2/annual_reminder/test_mode
     *
     * @param int|null $storeId
     *     Optional store ID. If omitted, falls back to helper's getStoreId().
     *
     * @return bool
     *     TRUE if Test Mode is enabled for the store.
     */
    public function isTestModeEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH . 'test_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Returns the configured Test Mode timestamp string.
     *
     * Used only when Test Mode is enabled. The cron job interprets this string
     * in the store timezone and overrides the "today" date, letting QA simulate
     * any day for testing reminder sending logic.
     *
     * Reads: abbott_sarp2/annual_reminder/test_date_time
     *
     * @param int|null $storeId
     *     Optional store ID. If omitted, falls back to helper's getStoreId().
     *
     * @return string
     *     A date‑time string (e.g., "2026‑01‑08 00:00:00") or empty string if none configured.
     */
    public function getTestmodeTimestamp(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH . 'test_date_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
    
    /**
     * GetBcc emails
     *
     * @return string
     */
    public function getBccEmailId(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'bcc', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

     /**
     * Retrieve selected store views for backorder email notifications.
     *
     * This method reads the configuration value from:
     * `additional_info/backorder_email/allowed_store_views`
     * and returns an array of store IDs. If no stores are selected,
     * it returns an empty array.
     *
     * @return array<int> List of selected store IDs.
     */
    public function getSelectedStores()
    {
        $selectedStores = $this->scopeConfig->getValue(self::XML_PATH . 'allowed_store_views', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return $selectedStores ? explode(',', $selectedStores) : [];
    }

     /**
     * Get the configured start date anchor as UTC 'Y-m-d H:i:s'.
     * @return string
     */
    public function getStartDateAnchor(?int $storeId = null): string
    {   
        $value = $this->scopeConfig->getValue(self::XML_PATH . 'start_date_anchor', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        // If only date provided (YYYY-MM-DD), append midnight.
        if ($value && strlen($value) === 10) {
            $value .= ' 00:00:00';
        }

        // Normalize to UTC
        try {
            $configTz = $this->timezone->getConfigTimezone(\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()) ?: 'UTC';
            $dt = new \DateTime($value ?: '2023-01-01 00:00:00', new \DateTimeZone($configTz));
            $dt->setTimezone(new \DateTimeZone('UTC'));
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Fallback to default if parsing fails
            return '2023-01-01 00:00:00';
        }
    }
}
