<?php
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Cron;

use Abbott\MetabolicOrdering\Helper\Data as Config;
use Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic\CollectionFactory;
use Abbott\MetabolicOrdering\Model\NotificationService;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeService;
use Psr\Log\LoggerInterface;

/**
 * Cron job: Sends  reminders to customers for product
  * against a configured threshold and send notification emails when a customer's
 * metabolic record goes at or below threshold and hasn't already been notified.
 */
class RunOutAfterOrder
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
        $this->config             = $config;
        $this->logger             = $logger;
        $this->collectionFactory  = $collectionFactory;
        $this->notificationService = $notificationService;
    }

    /**
     * Cron entry point: determines the target date based on store timezone,
     * finds eligible records, emails customers, and marks records as processed.
     *
     * @return void
     */
    public function execute(): void
    {
        try {
          // Feature toggle
            if (!$this->config->thresholdEnabled()) {
                return;
            }

            // Threshold configuration
            $threshold  = max(0, (int)$this->config->thresholdQty());

            // Determine STORE-LOCAL "today" (Y-m-d) for expiry comparison
            $today = $this->config->getToday();


            // 3) Build collection of items to notify
            $collection = $this->collectionFactory->create();
            $collection
                ->addFieldToFilter('qty', ['lteq' => $threshold])
                ->addFieldToFilter('expiry_date', ['gt' => $today])
                ->addFieldToFilter('enable_email', ['eq' => 1])
                ->addFieldToFilter('threshold_email_sent', ['eq' => 0]);

            if ($collection->getSize()) {
                // 4) Iterate and process each record
               foreach($collection as $metabolicItem)
               {
                    $this->notificationService->sendRunOutEmail($metabolicItem);
               }
            }
        } catch (\Exception $e) {
            // Top-level exception to avoid disrupting the cron schedule
            $this->logger->info('[Abbott_MetabolicOrdering] Cron failure in SendExpiryReminders: ' . $e->getMessage());
        }
    }
}