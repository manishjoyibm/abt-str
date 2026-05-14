<?php

namespace Abbott\Targetbase\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;

class Customersave implements ObserverInterface
{

    /**
     * @var \Abbott\Targetbase\Model\BaseTargetbase
     */
    protected $baseTargetbase;

    /**
     * @var \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory
     */
    protected $targetbaseCollectionFactory;

    /**
     * Customersave constructor.
     * @param \Abbott\Targetbase\Model\BaseTargetbase $baseTargetbase
     * @param \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory $targetbaseCollectionFactory
     */
    public function __construct(
        \Abbott\Targetbase\Model\BaseTargetbase $baseTargetbase,
        \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory $targetbaseCollectionFactory
    ) {
        $this->baseTargetbase = $baseTargetbase;
        $this->targetbaseCollectionFactory = $targetbaseCollectionFactory;
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
        $customer = $observer->getCustomer();
        if ($customer->getId()) {
            $tbCustomerCol = $this->targetbaseCollectionFactory->create()->addFieldToFilter(
                'status',
                'pending'
            )->addFieldToFilter('customer_id', $customer->getId());
            if ($tbCustomerCol->getSize()) {
                $tbCustomer=$tbCustomerCol->getFirstItem();
                $tbCustomer->setIsAddressChange(1);
                $tbCustomer->save();
            } else {
                if ($customer->getCreatedAt() == $customer->getUpdatedAt()) {
                    $type = "register";
                    $order = 0;
                    $this->baseTargetbase->insertData($customer->getId(), $customer->getStoreId(), $type, $order);
                } else {
                    $type = "address";
                    $order = 0;
                    $this->baseTargetbase->insertData($customer->getId(), $customer->getStoreId(), $type, $order);
                }
            }
        }
    }
}
