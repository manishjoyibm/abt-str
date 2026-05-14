<?php

namespace Abbott\Strongmoms\Helper;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_SSMUSER = 'aboott_message/ssm_user_notes/messagenote';
    public const XML_PATH_SUBSCRIPTIONUSER =
        'aboott_message/subscription_user_notes/subscriptionusermessage';

     /**
      * @var Session
      */
    protected $customerSession;

     /**
      * @var CollectionFactory
      */
    protected $orderCollectionFactory;

    protected $orders;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $addressRepository;

    protected $request;

    protected $resource;

    public const IS_SIMILAC_SSM = "similac-ssm";

    /**
     * Construct function
     *
     * @param Context $context
     * @param Session $customerSession
     * @param CollectionFactory $orderCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param AddressRepositoryInterface $addressRepository
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $orderCollectionFactory,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        AddressRepositoryInterface $addressRepository,
        ResourceConnection $resource
    ) {
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->addressRepository = $addressRepository;
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * Check customer is ssm or not in new similac
     *
     * @return boolean
     */
    public function isSSM()
    {
        $isSSMUser = false;
        $customer = $this->customerSession->getCustomer();
        if (!empty($customer->getData('user_type')) &&
            strtolower(trim($customer->getData('user_type'))) == self::IS_SIMILAC_SSM) {
            $isSSMUser = true;
        }
        return $isSSMUser;
    }

    /**
     * Check SSM customer order count
     *
     * @return integer
     */
    public function getSsmUserOrderCount()
    {

        $customerId = $this->customerSession->getCustomer()->getId();
        if (!$this->orders) {
            $this->orders = $this->orderCollectionFactory->create()
                ->addFieldToSelect('entity_id')
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder('created_at', 'desc')
                ->getSize();
        }
        return $this->orders;
    }

    /**
     * Get ssm user Notes from backend system
     *
     * @return string
     */
    public function getSsmUserConfig()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SSMUSER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get ssm user Notes from backend system
     *
     * @return string
     */
    public function getsubscriptionUserConfig()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUBSCRIPTIONUSER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get store id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get page
     *
     * @return mixed
     */
    public function getPage()
    {
        return $this->request->getParam('id');
    }

    /**
     * Check existing Profile
     *
     * @return bool
     */
    public function getuserSubscription()
    {
        $customerProfileExist = false;
        $customerId = $this->customerSession->getCustomer()->getId();
        $connection = $this->resource->getConnection();
        $tableName = 'aw_sarp2_profile';
        $select = $connection->select()->from(['sarp2_profile' => $this->resource->getTableName($tableName)])
        ->where('sarp2_profile.customer_id='.$customerId);
        if (!empty($connection->fetchRow($select))) {
            $customerProfileExist = true;
        }
        return $customerProfileExist;
    }
}
