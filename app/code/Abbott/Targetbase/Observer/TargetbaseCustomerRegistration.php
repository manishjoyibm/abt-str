<?php

namespace Abbott\Targetbase\Observer;

use Magento\Framework\Event\ObserverInterface;

class TargetbaseCustomerRegistration implements ObserverInterface
{
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
     * @var \Abbott\Targetbase\Model\BaseTargetbase
     */
    protected $baseTargetbase;
    /**
     * TargetbaseCustomerRegistration constructor.
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Abbott\Targetbase\Model\BaseTargetbase
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Abbott\Targetbase\Model\BaseTargetbase $baseTargetbase
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
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
        $customer = $observer->getEvent()->getCustomer();
        $checkSubscriber = $this->_subscriber->loadByEmail($customer->getEmail());
        $subscriptionstatus = ($checkSubscriber->isSubscribed()) ? 1 : 0;
        $type = ($subscriptionstatus) ? "register_subscription" : "register";
        $order = 0;
        $this->baseTargetbase->insertData($customer->getId(), $customer->getStoreId(), $type, $order);
    }
}
