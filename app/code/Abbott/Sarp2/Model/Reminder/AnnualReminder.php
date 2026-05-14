<?php
declare(strict_types=1);

namespace Abbott\Sarp2\Model\Reminder;

use Abbott\Sarp2\Helper\Data as ConfigHelper;
use Abbott\Sarp2\Model\Reminder\Sender;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Abbott\Sarp2\Logger\Logger;
use Abbott\Sarp2\Model\ResourceModel\Engine\Notification as NotificationResource;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;

/**
 * Annual renewal reminder Businness logic.
 *
 * Sends one email per customer when (next anniversary - N days) == today (store TZ).
 * If DaysBefore = 0, the reminder is sent ON the anniversary date.
 *
 * Idempotency: Sender::isAlreadySent(profile_id, renewal_year)
 */
class AnnualReminder
{
    private ResourceConnection $resource;
    private TimezoneInterface $timezone;
    private StoreManagerInterface $storeManager;
    private ConfigHelper $config;
    private Sender $sender;
    private Logger $logger;
    private $notificationResource;
    private $profileRepository;
    private $profileCollectionFactory;

    public function __construct(
        ResourceConnection $resource,
        TimezoneInterface $timezone,
        StoreManagerInterface $storeManager,
        ConfigHelper $config,
        Sender $sender,
        Logger $logger,
        ProfileRepositoryInterface $profileRepository,
        CollectionFactory $profileCollectionFactory,
        NotificationResource $notificationResource

    ) {
        $this->resource     = $resource;
        $this->timezone     = $timezone;
        $this->storeManager = $storeManager;
        $this->config       = $config;
        $this->sender       = $sender;
        $this->logger       = $logger;
        $this->profileRepository = $profileRepository;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->notificationResource = $notificationResource;
    }

    /**
     * Process one store:
     * - Compute "today" at midnight in store TZ.
     * - Load active profiles for the store (no SQL date pre-filtering).
     * - Compute each profile's next anniversary (handles leap-year anchors).
     * - Compute reminderDate = anniversary - DaysBefore (special-case 0).
     * - Send if reminderDate == today and not already sent for (profile, year).
     */
    public function processStore(int $storeId): void
    {
        $today      = $this->getTodayImmutable($storeId)->setTime(0, 0, 0);
        $daysBefore = max(0, (int)$this->config->getDaysBefore($storeId)); // 0 = same-day.
        $startAnchor = $this->config->getStartDateAnchor($storeId); 
        
        // Load active/ongoing profiles for this store
       
        $profiles = $this->profileCollectionFactory->create();
        $profiles->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('status', ['eq' => 'active'])
                ->addFieldToFilter('start_date', ['gteq' => $startAnchor]);

        if (!$profiles) {
            return;
        }

        /** @var array<string, array<int, array<string, mixed>>> $dueByCustomer */
        $dueByCustomer = [];

        foreach ($profiles as $profile) {
        $row = $profile->getData();

            $anchorStr = $this->resolveAnchorDate($row);
            if (!$anchorStr) {
                continue;
            }
            $anchor = $this->safeImmutable($anchorStr, $today->getTimezone());
            if (!$anchor) {
                continue;
            }

            // Compute the next anniversary on/after "today".
            $anniversary = $this->nextAnniversary($anchor, $today);

            // Apply "Days Before" rule:
            // - If 0: send ON the anniversary (no reliance on modify('0 days')).
            // - Else: anniversary minus N days.
            if ($daysBefore === 0) {
                $reminderDate = $anniversary;
            } else {
                $reminderDate = $anniversary->modify(sprintf('-%d days', $daysBefore));
            }
            // Normalize both to midnight to be absolutely safe.
            $reminderDate = $reminderDate->setTime(0, 0, 0);
            $todayMid     = $today->setTime(0, 0, 0);

            if ($reminderDate->format('Y-m-d') !== $todayMid->format('Y-m-d')) {
                continue; // not today's send
            }

            $profileId   = (int)$row['profile_id'];
            $renewalYear = (int)$anniversary->format('Y');

            // Idempotency guard
            if ($this->sender->isAlreadySent($profileId, $renewalYear)) {
                continue;
            }

             // OPTIONAL trace (keep or remove):
            $this->logger->info(sprintf(
                '[AnnualReminderCron] p#%s email=%s',
                (string)$row['profile_id'],
                (string)$row['customer_email'],
            ));

            // Load items for email rows
            $items = isset($row['items']) ? $row['items'] : [];

            // Optional enrichment: get upcoming billing info from notifications
            $nextPayment = $this->getNextUpcomingBillingDate($storeId, $profileId);
            
            $email = (string)($row['customer_email'] ?? '');
            if ($email === '') {
                continue;
            }

            $dueByCustomer[$email][] = [
                'profile_id'              => $profileId,
                'increment_id'            => (string)$row['increment_id'],
                'start_date'              => (string)$row['start_date'],
                'plan_name'               => (string)$row['plan_name'],
                'renewal'                 => $anniversary, // \DateTimeImmutable
                'next_payment_date'       => $nextPayment['nextPaymentDate'] ?? null,
                'next_payment_amount'     => $nextPayment['nextPaymentTotalAmount'] ?? null,
                'items'                   => $items,
                'store_id'                => $storeId,
                'customer_id'             => (int)($row['customer_id'] ?? 0),
                'customer_email'          => $email,
                'bcc_emails'              => $this->config->getBccEmailId($storeId)
            ];
        }
        // Send one cumulative email per customer
        foreach ($dueByCustomer as $email => $entries) {
            try {
                $this->sender->sendCumulative($entries, $storeId);
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf('[Abbott_Sarp2] Sending reminder failed for %s (storeId=%d): %s', $email, $storeId, $e->getMessage()),
                    ['exception' => $e]
                );
            }
        }
    }

    /**
     * Read most recent "upcoming_billing" notification for profile (optional enrichment).
     *
     * @return array{
     *   nextPaymentDate: string|null,
     *   nextPaymentTotalAmount: string|null
     * }|null
     */
    private function getNextUpcomingBillingDate(
        int $storeId,
        int $profileId
    ): ?array {
        $row = $this->notificationResource->getLatestUpcomingBilling($profileId, $storeId);
        if (!$row || empty($row['notification_data'])) {
            return null;
        }
        try {
            $data = json_decode((string)$row['notification_data'], true);
            if (!is_array($data)) {
                return null;
            }
            $rawAmount   = (string)($data['finalPrice'] ?? $data['nextPaymentTotalAmount'] ?? '');
            $cleanAmount = trim(strip_tags($rawAmount));
            return [
                'nextPaymentDate'        => $data['nextPaymentDate'] ?? null,
                'nextPaymentTotalAmount' => $cleanAmount ?: null
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Store-local midnight "today".
     * If test mode is enabled in config, uses the configured timestamp (store TZ).
     */
    private function getTodayImmutable(int $storeId): \DateTimeImmutable
    {
        $storeNowMutable = $this->timezone->date(); // DateTime (store TZ)
        $storeTz         = $storeNowMutable->getTimezone(); // DateTimeZone
        $todayImmutable  = (new \DateTimeImmutable('now', $storeTz))->setTime(0, 0, 0);

        if ($this->config->isTestModeEnabled($storeId)) {
            try {
                $todayImmutable = new \DateTimeImmutable($this->config->getTestmodeTimestamp($storeId), $storeTz);
            } catch (\Exception $e) {
                $todayImmutable = new \DateTimeImmutable('now', $storeTz);
            }
        }
        return $todayImmutable;
    }

    /**
     * Prefers 'start_date'; falls back to 'created_at'.
     */
    private function resolveAnchorDate(array $row): ?string
    {
        if (!empty($row['start_date'])) {
            return (string)$row['start_date'];
        }
        if (!empty($row['created_at'])) {
            return (string)$row['created_at'];
        }
        return null;
    }

    /**
     * Safe factory for DateTimeImmutable with TZ.
     */
    private function safeImmutable(string $dateStr, \DateTimeZone $tz): ?\DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($dateStr, $tz);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Next anniversary date (>= today) from anchor, normalized to midnight.
     * Handles Feb 29 anchors -> Feb 28 in non-leap years.
     */
    private function nextAnniversary(\DateTimeImmutable $anchor, \DateTimeImmutable $today): \DateTimeImmutable
    {
        $tz    = $today->getTimezone();
        $month = (int)$anchor->format('n'); // 1-12
        $day   = (int)$anchor->format('j'); // 1-31
        $year  = (int)$today->format('Y');

        // Candidate for current year
        $candidate = $this->anniversaryForYear($month, $day, $year, $tz);
        if ($candidate < $today) {
            $candidate = $this->anniversaryForYear($month, $day, $year + 1, $tz);
        }
        return $candidate;
    }

    /**
     * Anniversary for specific year; adjusts Feb 29 -> Feb 28 in non-leap years.
     */
    private function anniversaryForYear(int $anchorMonth, int $anchorDay, int $year, \DateTimeZone $tz): \DateTimeImmutable
    {
        if ($anchorMonth === 2 && $anchorDay === 29 && !$this->isLeapYear($year)) {
            $anchorDay = 28;
        }
        return (new \DateTimeImmutable('now', $tz))
            ->setDate($year, $anchorMonth, $anchorDay)
            ->setTime(0, 0, 0);
    }

    private function isLeapYear(int $year): bool
    {
        return ($year % 400 === 0) || ($year % 4 === 0 && $year % 100 !== 0);
    }
}