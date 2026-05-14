<?php

namespace Abbott\Targetbase\Observer;

use Magento\Framework\Event\ObserverInterface;

class TargetbaseAddressChange implements ObserverInterface
{
    public $_customerSession;
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    public $_subscriber;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $subscriber;
    /**
     * @var \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory
     */
    protected $targetbaseCollectionFactory;
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;
    /**
     * @var \Abbott\Targetbase\Model\BaseTargetbase
     */
    protected $baseTargetbase;

    /**
     * TargetbaseAddressChange constructor.
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory $targetbaseCollectionFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Abbott\Targetbase\Model\BaseTargetbase
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory $targetbaseCollectionFactory,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Abbott\Targetbase\Model\BaseTargetbase $baseTargetbase
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerSession = $customerSession->create();
        $this->targetbaseCollectionFactory = $targetbaseCollectionFactory;
        $this->_subscriber= $subscriber;
        $this->baseTargetbase = $baseTargetbase;
    }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_customerSession->getId()) {
            $customerid= $this->_customerSession->getId();
            $customer = $this->_customerRepositoryInterface->getById($customerid);
            $tbCustomerCol = $this->targetbaseCollectionFactory->create()->addFieldToFilter(
                'status',
                'pending'
            )->addFieldToFilter(
                'customer_id',
                $customerid
            )->addFieldToFilter(
                'order_id',
                ['eq' => 0]
            );
            if ($tbCustomerCol->getSize()) {
                $tbCustomer=$tbCustomerCol->getFirstItem();
                $tbCustomer->setIsAddressChange(1);
                $tbCustomer->save();
            } else {
                $type = "address";
                $order = 0;
                $this->baseTargetbase->insertData($customer->getId(), $customer->getStoreId(), $type, $order);
            }
        }
    }
}
