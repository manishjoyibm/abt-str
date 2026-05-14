<?php

namespace Abbott\Impersonation\Model;

/**
 * Login model
 */
class Impersonation extends \Magento\Framework\Model\AbstractModel
{

    public $accountHelper;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_random;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Math\Random $random,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Abbott\MyAccount\Helper\Data $accountHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_random = $random;
        $this->_checkoutSession = $checkoutSession;
        $this->accountHelper = $accountHelper;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Abbott\Impersonation\Model\ResourceModel\Impersonation::class);
    }

    /**
     * Retrieve customer
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            $this->_customer = $this->_customerFactory->create()
                ->load($this->getCustomerId());
        }
        return $this->_customer;
    }

    /**
     * Generate new login credentials
     * @param  int $adminId
     * @return $this
     */
    public function generate($adminId)
    {
        return $this->setData([
            'customer_id' => $this->getCustomerId(),
            'admin_id' => $adminId,
            'secret' => $this->_random->getRandomString(64),
            'created_at' => $this->_dateTime->gmtTimestamp()
        ])->save();
    }

    /**
     * Login Customer
     * @return false || \Magento\Customer\Model\Customer
     */
    public function authenticateCustomer()
    {
        

        if ($this->_customerSession->getId()) {
            /* Logout if logged in */
            $this->_customerSession->logout();
            $this->accountHelper->removeCookie('abt_usr');
            $this->accountHelper->removeCookie('abt_sesCartKey');
            $this->accountHelper->removeCookie('abt_cartKey');
            $this->accountHelper->removeCookie('abt_asm');
            $this->accountHelper->removeCookie('abt_te');
            $this->accountHelper->removeCookie('form_key');
            $this->accountHelper->removeCookie('abt_sgp');
            $this->accountHelper->removeCookie('abt_psrid');
            $this->accountHelper->removeCookie('mage_usr_imp');
            
        }

        $customer = $this->getCustomer();

        if (!$customer->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(__("Customer are no longer exist."))
            );
        }

        if ($this->_customerSession->loginById($customer->getId())) {
            $this->_customerSession->regenerateId();
            $this->_customerSession->setLoggedAsCustomerAdmindId(
                $this->getAdminId()
            );
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(__("Cannot login customer."))
            );
        }

        /* Load Customer Quote */
        $this->_checkoutSession->loadCustomerQuote();
        $quote = $this->_checkoutSession->getQuote();
        $quote->setCustomerIsGuest(0);
        $quote->save();

        return $customer;
    }

    /**
     * Retrieve not used admin login
     * @param  string $secret
     * @return self
     */
    public function loadNotUsed($secret)
    {
        return $this->getCollection()
            ->addFieldToFilter('secret', $secret)
            ->getFirstItem();
    }

    /**
     * Retrieve login datetime point
     * @return [type] [description]
     */
    protected function getDateTimePoint()
    {
        return date('Y-m-d H:i:s', $this->_dateTime->gmtTimestamp() - self::TIME_FRAME);
    }

    public function clearCookie()
    { 
       
        if ($this->_customerSession->getId()) {
            /* Logout if logged in */
            $this->_customerSession->logout();
            $this->accountHelper->removeCookie('abt_usr');
            $this->accountHelper->removeCookie('abt_sesCartKey');
            $this->accountHelper->removeCookie('abt_cartKey');
            $this->accountHelper->removeCookie('abt_asm');
            $this->accountHelper->removeCookie('abt_te');
            $this->accountHelper->removeCookie('form_key');
            $this->accountHelper->removeCookie('abt_sgp');
            $this->accountHelper->removeCookie('abt_psrid');
			$this->accountHelper->removeCookie('x-id-token');
        }
        
    }

}
