<?php
namespace Abbott\RestrictCheckout\Model;

use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Restriction extends \Magento\Framework\Model\AbstractModel
{
    public $storeManager;
    private const RESTRICTION_ORDER_LIMIT = 'restrictcheckout/restrictcheckout/restrictcheckout_order_limit';
    private const RESTRICTION_MESSAGE = 'restrictcheckout/restrictcheckout/restrictcheckout_message';
    private const RESTRICTION_CUSTOMER_GROUP = 'restrictcheckout/restrictcheckout/restrictcheckout_customergroup';
    private const RESTIME = "Y-m-d H:i:s";
    private const CUSTEMAIL = 'customer_email';
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var AccountHelper
     */
    protected $accountHelper;
    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * constructor.
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param CollectionFactory $orderCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param AccountHelper $accountHelper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        CollectionFactory $orderCollectionFactory,
        StoreManagerInterface $storeManager,
        AccountHelper $accountHelper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->storeManager = $storeManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->accountHelper = $accountHelper;
    }

    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setRestrictionDetails()
    {
        if ($this->validateCustomerGroup()) {
            $orderTotal = $this->getOrderTotalForCustomer();
            $orderLimit = $this->getOrderLimit();
            $message = $this->getMessage();
            $cookieDomain = $this->accountHelper->getCookieRedirect();
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setDomain($cookieDomain);
            $publicCookieMetadata->setHttpOnly(false);
            $publicCookieMetadata->setSameSite('Lax');
            $this->cookieManager->setPublicCookie(
                'abt_sgp',
                '{"limit":' . $orderLimit . ',"message":"' . $message . '","ordertotal":' . $orderTotal . '}',
                $publicCookieMetadata
            );
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateCustomerGroup()
    {
        if (
            $this->storeManager->getStore()->getId()==AccountHelper::ABT_STORE_ID
            && $this->customerSession->isLoggedIn()
        ) {
            $restrictedGroups = explode(",", $this->getCustomerGroups());
            $allowOrder = isset($this->customerSession->getCustomer()->getData()['allow_order_limit'])
                ? $this->customerSession->getCustomer()->getData()['allow_order_limit'] : 0;
            if (in_array($this->customerSession->getCustomer()->getGroupId(), $restrictedGroups) && !$allowOrder) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderTotalForCustomer()
    {
        $orderTotal = 0;
        if ($this->validateCustomerGroup()) {
            $todayDate = $this->dateTime->date(self::RESTIME);
            $fromDate = date(self::RESTIME, strtotime('-30 days', strtotime($todayDate))); // start date
            $toDate = date(self::RESTIME, strtotime('+23 hours 59 minutes', strtotime($todayDate))); // end date
            $ordercollection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter('created_at', ['to'=>$toDate, 'from'=>$fromDate])
                ->addFieldToFilter('store_id', ['eq' => $this->storeManager->getStore()->getId()])
                ->addFieldToFilter(self::CUSTEMAIL, ['eq' => $this->customerSession->getCustomer()->getEmail()])
                ->addFieldToFilter('status', ['neq' => 'canceled'])
                ->addFieldToSelect(['created_at' ,'store_id', self::CUSTEMAIL,'status' , 'subtotal','discount_amount'])
                ->addExpressionFieldToSelect(
                    'ordertotal',
                    'SUM(subtotal+(case WHEN discount_amount is null Then 0.000 ELSE discount_amount END))',
                    ['subtotal','discount_amount']
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
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderLimit()
    {
        return $this->scopeConfig->getValue(
            self::RESTRICTION_ORDER_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMessage()
    {
        $message = $this->scopeConfig->getValue(
            self::RESTRICTION_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        $orderLimit = $this->getOrderLimit();
        return str_replace("{{ordertotal}}", $orderLimit, $message);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerGroups()
    {
        return $this->scopeConfig->getValue(
            self::RESTRICTION_CUSTOMER_GROUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }
}
