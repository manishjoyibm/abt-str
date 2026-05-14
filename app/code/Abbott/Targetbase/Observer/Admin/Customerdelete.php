<?php

namespace Abbott\Targetbase\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Setup\Exception;

class Customerdelete implements ObserverInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory
     */
    protected $targetbaseCollectionFactory;

    /**
     * Customerdelete constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory $targetbaseCollectionFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Abbott\Targetbase\Model\ResourceModel\Targetbase\CollectionFactory $targetbaseCollectionFactory
    ) {
        $this->logger = $logger;
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
        $tbCustomerCol = $this->targetbaseCollectionFactory->create()
                              ->addFieldToFilter('status', 'pending')
                              ->addFieldToFilter('customer_id', $customer->getId())
                              ->addFieldToFilter('order_id', ['eq' => 0]);

        if ($tbCustomerCol->getSize() > 0) {
            foreach ($tbCustomerCol as $customer) {
                try {
                    $customer->delete();
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }
    }
}
