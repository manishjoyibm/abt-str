<?php
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Abbott\MetabolicOrdering\Helper\Data as Config;
use Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic\CollectionFactory;
use Abbott\MetabolicOrdering\Model\NotificationService;
use Psr\Log\LoggerInterface;

/**
 * Observer: After an order is placed (admin/frontend), check affected SKUs
 * against a configured threshold and send notification emails when a customer's
 * metabolic record goes at or below threshold and hasn't already been notified.
*
 */
class RunoutAfterOrder implements ObserverInterface
{
    /** @var Config */
    protected $config;

    /** @var LoggerInterface */
    protected $logger;

    /** @var CollectionFactory */
    protected $collectionFactory;

        /** @var NotificationService */
    protected $notificationService;
   

    /**
     * DI Constructor.
     *
     * @param Config                       $config
     * @param LoggerInterface              $logger
     * @param CollectionFactory            $collectionFactory
     * @param NotificationService          $notificationService
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        NotificationService $notificationService
    ) {
        ;
        $this->config             = $config;
        $this->logger             = $logger;
        $this->collectionFactory  = $collectionFactory;
        $this->notificationService = $notificationService;    }

    /**
     * Execute observer logic after order placement.
     * - Checks feature toggle
     * - For each ordered SKU, finds matching metabolic records
     *   where qty <= threshold, expiry_date > today, not yet emailed,
     *   and belongs to the ordering customer.
     * - Sends email and marks the record as notified.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Feature toggle
        if (!$this->config->thresholdEnabled()) {
            return;
        }

        // Threshold configuration
        $threshold  = max(0, (int)$this->config->thresholdQty());

        // Determine STORE-LOCAL "today" (Y-m-d) for expiry comparison
        $today = $this->config->getToday();

        // Get the order from the event
        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            return;
        }

        $customerEmail = (string)$order->getCustomerEmail();
        if (!$customerEmail) {
            return;
        }

        // Iterate ordered items
        foreach ($order->getAllVisibleItems() as $observerItem) {
            $sku = (string)$observerItem->getSku();
            if ($sku === '') {
                continue;
            }

            // Build collection of candidate records for this SKU & customer
            $collection = $this->collectionFactory->create();
            $collection
                ->addFieldToFilter('sku', $sku)
                ->addFieldToFilter('qty', ['lteq' => $threshold])
                ->addFieldToFilter('expiry_date', ['gt' => $today])
                ->addFieldToFilter('threshold_email_sent', ['eq' => 0])
                ->addFieldToFilter('enable_email', ['eq' => 1])
                ->addFieldToFilter('customer_email', ['eq' => $customerEmail]);
            
            if (!$collection->getSize()) {
                continue;
            }

           foreach ($collection as $metabolicItems){
            $this->notificationService->sendRunOutEmail($metabolicItems);
           }
        }
    }

}
