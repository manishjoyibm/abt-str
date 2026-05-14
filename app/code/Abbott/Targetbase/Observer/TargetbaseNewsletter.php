<?php

namespace Abbott\Targetbase\Observer;

use Magento\Framework\Event\ObserverInterface;

class TargetbaseNewsletter implements ObserverInterface
{
    public $_customerSession;
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
     * @var \Abbott\Targetbase\Model\TargetbaseFactory
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
     * TargetbaseNewsletter constructor.
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
        $isSub = $observer->getEvent()->getRequest()->getParam('isSubscribed');

        $subs = ($isSub) ? 1 : 0;

        $customerid= $this->_customerSession->getId();
        $customer = $this->_customerRepositoryInterface->getById($customerid);

        $checkSubscriber = $this->_subscriber->loadByEmail($customer->getEmail());

        $subscriptionStatus = ($checkSubscriber->isSubscribed()) ? 1 : 0;

        $subscriptionChangeStatus = ($subscriptionStatus==$subs) ? 0 : 1;

        if ($subscriptionChangeStatus) {
            $tbCustomerCol = $this->targetbaseCollectionFactory->create()->addFieldToFilter(
                'status',
                'pending'
            )->addFieldToFilter(
                'customer_id',
                $customerid
            );
            if ($tbCustomerCol->getSize()) {
                $tbCustomer=$tbCustomerCol->getFirstItem();
                $tbCustomer->setContactPreference($subscriptionChangeStatus);
                $tbCustomer->save();
            } else {
                $type = "subscription";
                $order = 0;
                $this->baseTargetbase->insertData($customer->getId(), $customer->getStoreId(), $type, $order);
            }
        }
    }
}
