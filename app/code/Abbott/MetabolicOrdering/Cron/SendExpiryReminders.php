<?php
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Cron;

use Abbott\MetabolicOrdering\Helper\Data as Config;
use Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic\CollectionFactory;
use Abbott\MetabolicOrdering\Model\NotificationService;
use Psr\Log\LoggerInterface;

/**
 * Cron job: Sends expiry reminders to customers for products that will
 * expire after a configured number of days from the current store date.
 *
 * Flow:
 * 1) Check feature toggle from config
 * 2) Compute store-local target date = today + N days (using TimezoneInterface)
 * 3) Pull records with qty > 0, expiry_date == target date, not emailed yet
 * 4) Send email using a template and mark items as emailed with UTC timestamp
 */
class SendExpiryReminders
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
            // 1) Feature toggle from module config
            if (!$this->config->expiryEnabled()) {
                return;
            }

            // 2) Compute target date in STORE TIMEZONE: today + N days
            $targetDate = $this->config->getTargetDate();

            // 3) Build collection of items to notify
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('qty', ['gt' => 0])
                ->addFieldToFilter('expiry_date', ['eq' => $targetDate])
                ->addFieldToFilter('enable_email', ['eq' => 1])
                ->addFieldToFilter('expiry_email_sent', ['eq' => 0]);

            if ($collection->getSize()) {
                // 4) Iterate and process each record
               foreach($collection as $metabolicItem)
               {
                    $this->notificationService->sendExpiryEmail($metabolicItem);
               }
            }
        } catch (\Exception $e) {
            // Top-level exception to avoid disrupting the cron schedule
            $this->logger->info('[Abbott_MetabolicOrdering] Cron failure in SendExpiryReminders: ' . $e->getMessage());
        }
    }
}