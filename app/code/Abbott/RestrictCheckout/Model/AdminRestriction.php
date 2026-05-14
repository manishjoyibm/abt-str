<?php
namespace Abbott\RestrictCheckout\Model;

use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class AdminRestriction extends \Magento\Framework\Model\AbstractModel
{
    public $_scopeConfig;
    const RESTRICTION_ORDER_LIMIT = 'restrictcheckout/restrictcheckout/restrictcheckout_order_limit';
    const RESTRICTION_MESSAGE = 'restrictcheckout/restrictcheckout/restrictcheckout_message';
    const RESTRICTION_CUSTOMER_GROUP = 'restrictcheckout/restrictcheckout/restrictcheckout_customergroup';
    const RESTIME = "Y-m-d H:i:s";
    const CUSTEMAIL = 'customer_email';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;
    /**
     * @var DateTime
     */
    protected DateTime $dateTime;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $orderCollectionFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        CollectionFactory $orderCollectionFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
    }
    /**
     * For Restricting admin , for placing order
     *
     * @return string
     */
    public function getAdminRestrictionDetails($customerEmail, $subtotal, $storeId, $customerGroupId, $quote)
    {
        $message='';
        if ($this->validateCustomerGroup($storeId, $customerGroupId, $quote)) {
            $customerData = $this->customerRepository->get($customerEmail, $storeId);
            $allowOrder = ($customerData->getCustomAttribute('allow_order_limit'))
                ? $customerData->getCustomAttribute('allow_order_limit')->getValue() : 0;
            $orderTotal = $this->getOrderTotalForCustomer($storeId, $customerEmail, $customerGroupId, $quote);
            $orderLimit = $this->getOrderLimit($storeId);
            if (($orderTotal + $subtotal) > $orderLimit && !$allowOrder) {
                $message = $this->getMessage($storeId);
            }
        }
        return $message;
    }
    /**
     * For Validating Customer Group
     *
     * @return bool
     *
     */
    public function validateCustomerGroup($storeId, $customerGroupId, $quote)
    {
        if ($storeId == AccountHelper::ABT_STORE_ID) {
            $restrictedGroups = explode(",", $this->getCustomerGroups($storeId));
            if (in_array($customerGroupId, $restrictedGroups) && $this->proceedRestriction($quote)) {
                return true;
            }
        }

        return false;
    }
    /**
     * For Getting Order Total of a customer
     *
     * @return int
     *
     */
    public function getOrderTotalForCustomer($storeId, $customerEmail, $customerGroupId, $quote)
    {
        $orderTotal = 0;
        if ($this->validateCustomerGroup($storeId, $customerGroupId, $quote)) {
            $todayDate = $this->dateTime->date(self::RESTIME);
            $fromDate = date(self::RESTIME, strtotime('-30 days', strtotime($todayDate))); // start date
            $toDate = date(self::RESTIME, strtotime('+23 hours 59 minutes', strtotime($todayDate)));
            $ordercollection = $this->_orderCollectionFactory->create()
                    ->addFieldToFilter('created_at', ['to' => $toDate, 'from' => $fromDate])
                    ->addFieldToFilter('store_id', ['eq' => $storeId])
                    ->addFieldToFilter(self::CUSTEMAIL, ['eq' => $customerEmail])
                    ->addFieldToFilter('status', ['neq' => 'canceled'])
                    ->addFieldToSelect(
                        ['created_at', 'store_id', self::CUSTEMAIL, 'status', 'subtotal', 'discount_amount']
                    )
                    ->addExpressionFieldToSelect(
                        'ordertotal',
                        'SUM(subtotal+discount_amount)',
                        ['subtotal', 'discount_amount']
                    );
            $ordercollection->getSelect()->group(self::CUSTEMAIL);
            foreach ($ordercollection as $col) {
                $orderTotal = $col->getOrdertotal();
                break;
            }
        }

        return $orderTotal;
    }
    /**
     * For Getting Order limit
     *
     * @return mixed
     *
     */
    public function getOrderLimit($storeId)
    {
        return $this->_scopeConfig->getValue(
            self::RESTRICTION_ORDER_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * For Getting Limit Message
     *
     * @return string
     *
     */
    public function getMessage($storeId)
    {
        $message = $this->_scopeConfig->getValue(
            self::RESTRICTION_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $orderLimit = $this->getOrderLimit($storeId);
        return str_replace("{{ordertotal}}", $orderLimit, $message);
    }
    /**
     * For Getting Customer Groups
     *
     * @return mixed
     *
     */
    public function getCustomerGroups($storeId)
    {
        return $this->_scopeConfig->getValue(
            self::RESTRICTION_CUSTOMER_GROUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * For checking overide purchase limit
     *
     * @return bool
     *
     */
    public function proceedRestriction($quote)
    {
        $quoteAttributes = $quote->getExtensionAttributes();
        if ($quoteAttributes && $quoteAttributes->getAmastyOrderAttributes()) {
            $customAttributes = $quoteAttributes->getAmastyOrderAttributes();
            if (!empty($customAttributes)) {
                foreach ($customAttributes as $attribute) {
                    if ('override_purchase_limit' == $attribute->getAttributeCode() && $attribute->getValue() > 0) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
