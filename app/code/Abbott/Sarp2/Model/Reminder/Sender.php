<?php
declare(strict_types=1);

namespace Abbott\Sarp2\Model\Reminder;

use Abbott\Sarp2\Helper\Data as ConfigHelper;
use Abbott\Sarp2\Model\Reminder\RecordFactory;
use Abbott\Sarp2\Model\ResourceModel\Reminder\Record as RecordResource;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sends annual reminder emails grouped by subscription (profile_id) and
 * renders a single subscription header (with merged cells via rowspan)
 * followed by multiple product lines underneath.
 *
 * Also persists a single "sent" marker per subscription via upsert
 * (recommend UNIQUE index on profile_id in the reminder table).
 */
class Sender
{
    /** @var TransportBuilder */
    private TransportBuilder $transportBuilder;

    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;

    /** @var ConfigHelper */
    private ConfigHelper $config;

    /** @var PriceHelper */
    private PriceHelper $priceHelper;

    /** @var TimezoneInterface */
    private TimezoneInterface $tz;

    /** @var RecordFactory */
    private RecordFactory $recordFactory;

    /** @var RecordResource */
    private RecordResource $recordResource;

    /**
     * @param TransportBuilder      $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param ConfigHelper          $config
     * @param PriceHelper           $priceHelper
     * @param TimezoneInterface     $tz
     * @param RecordFactory         $recordFactory
     * @param RecordResource        $recordResource
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ConfigHelper $config,
        PriceHelper $priceHelper,
        TimezoneInterface $tz,
        RecordFactory $recordFactory,
        RecordResource $recordResource
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager     = $storeManager;
        $this->config           = $config;
        $this->priceHelper      = $priceHelper;
        $this->tz               = $tz;
        $this->recordFactory    = $recordFactory;
        $this->recordResource   = $recordResource;
    }

    /**
     * Optional helper to check if a row already exists for a subscription.
     * With DB UNIQUE(profile_id) + upsert, this is not strictly required.
     */
     public function isAlreadySent(int $profileId, int $year): bool
    {
        $connection = $this->recordResource->getConnection();
        $table      = $this->recordResource->getMainTable();

        $select = $connection->select()
            ->from($table, ['entity_id'])
            ->where('profile_id = ?', $profileId)
            ->where('renewal_year = ?', $year)
            ->limit(1);

        return (bool)$connection->fetchOne($select);
    }

    /**
     * Sends a single cumulative email for one customer and store, grouped by profile_id.
     * The HTML table shows a single subscription header row (merged with rowspan)
     * followed by product rows.
     *
     * Expected $entries shape (per subscription):
     *   - profile_id, increment_id, plan_name, start_date (string), renewal (\DateTimeImmutable)
     *   - next_payment_date (string), next_payment_amount (string)
     *   - customer_email, bcc_emails, customer_id
     *   - items: array of product rows; each item: ['name','sku','qty','regular_price']
     */
    public function sendCumulative(array $entries, int $storeId): void
    {
        if (!$entries) {
            return;
        }

        // === Group by subscription ID (profile_id) ===
        $byProfile = [];
        foreach ($entries as $e) {
            $pid = (int)($e['profile_id'] ?? 0);
            if (!$pid) {
                continue;
            }
            if (!isset($byProfile[$pid])) {
                $byProfile[$pid] = $e;
                $byProfile[$pid]['items'] = isset($e['items']) ? (array)$e['items'] : [];
            } else {
                // Merge items; preserve the first non-empty subscription meta fields
                $byProfile[$pid]['items'] = array_merge(
                    (array)$byProfile[$pid]['items'],
                    (array)($e['items'] ?? [])
                );

                foreach ([
                    'increment_id','plan_name','start_date',
                    'customer_email','bcc_emails','customer_id',
                    'next_payment_date','next_payment_amount'
                ] as $k) {
                    if (empty($byProfile[$pid][$k]) && !empty($e[$k])) {
                        $byProfile[$pid][$k] = $e[$k];
                    }
                }

                // If both have 'renewal' DateTimeImmutable, keep the earliest upcoming date
                if (!empty($e['renewal']) && ($e['renewal'] instanceof \DateTimeImmutable)) {
                    if (empty($byProfile[$pid]['renewal']) || !($byProfile[$pid]['renewal'] instanceof \DateTimeImmutable)) {
                        $byProfile[$pid]['renewal'] = $e['renewal'];
                    } else {
                        $curr = $byProfile[$pid]['renewal'];
                        if ($e['renewal'] < $curr) {
                            $byProfile[$pid]['renewal'] = $e['renewal'];
                        }
                    }
                }
            }
        }

        if (!$byProfile) {
            return;
        }

        $entriesGrouped = array_values($byProfile);

        // Recipient / URLs / Template
        $store    = $this->storeManager->getStore($storeId);
        $email    = (string)($entriesGrouped[0]['customer_email'] ?? '');
        $bccRaw   = (string)($entriesGrouped[0]['bcc_emails'] ?? '');
        $custId   = (int)($entriesGrouped[0]['customer_id'] ?? 0);
        $custName = $this->deriveCustomerName($email, $custId, $storeId);

        $itemsTableHtml = $this->buildItemsTableHtml($entriesGrouped, $storeId);

        $baseUrl       = rtrim($store->getBaseUrl(), '/');
        $accountUrl    = $baseUrl . '/customer/account';
        $cancelInfoUrl = $baseUrl . '/schedule-and-save.html';
        $contactUrl    = $baseUrl . '/contact';

        $vars = [
            'customer_name'     => $custName,
            'items_table_html'  => $itemsTableHtml,
            'account_url'       => $accountUrl,
            'cancel_info_url'   => $cancelInfoUrl,
            'contact_url'       => $contactUrl,
        ];

        $templateId = $this->config->getTemplateId($storeId) ?: 'abbott_sarp2_annual_reminder_template';
        $identity   = 'general';

        $transportBuilder = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($vars)
            ->setFromByScope($identity, $storeId)
            ->addTo($email);

        if (!empty($bccRaw)) {
            foreach (array_filter(array_map('trim', explode(',', $bccRaw))) as $bcc) {
                $transportBuilder->addBcc($bcc);
            }
        }

        $transport = $transportBuilder->getTransport();
        $transport->sendMessage();

        // Persist one row per subscription (idempotent)
        foreach ($entriesGrouped as $e) {
            $this->markSent((int)$e['profile_id'], (int)$e['renewal']->format('Y'));
        }
    }

    /**
     * Upsert (insert or update) a single row per subscription.
     * Requires UNIQUE KEY on (profile_id) in the reminder table.
     */
    private function markSent(int $profileId, int $year): void
    {
        $record = $this->recordFactory->create();
        $record->setData([
            'profile_id'   => $profileId,
            'renewal_year' => $year,
        ]);
        try {
            $this->recordResource->save($record);
        } catch (\Exception $e) {
            // ignore duplicate unique key errors (idempotency)
        }
    }
    
    /**
     * Build the HTML table to match the requested layout with merged cells:
     * - One set of subscription-level cells merged via rowspan across all products in that subscription.
     * - Product rows list Product(s) on Subscription, Price per Product, Qty.
     * - "Total Price of Subscription" appears once (merged cell with rowspan).
     */
    private function buildItemsTableHtml(array $entries, int $storeId): string
    {
        $rows = [];

        foreach ($entries as $e) {
            $subscriptionId   = (string)($e['increment_id'] ?? '');
            $startDateStr     = (string)($e['start_date'] ?? '');
            $planName         = (string)($e['plan_name'] ?? '');
            $nextPaymentDate  = (string)($e['next_payment_date'] ?? '-');
            $cancelBefore     = $nextPaymentDate;

            // Normalize items array
            $items = array_values((array)($e['items'] ?? []));
            $itemCount = count($items);

            // Compute subscription-level total from items
            $subscriptionTotal = 0.0;
            foreach ($items as $it) {
                $qty   = (float)($it['qty'] ?? 0);
                $price = (float)($it['regular_price'] ?? 0);
                $subscriptionTotal += $qty * $price;
            }
            $formattedSubscriptionTotal = $this->priceHelper->currency($subscriptionTotal, true, false);

            // If there are no items, print a single row with merged subscription cells and blanks for product columns
            if ($itemCount === 0) {
                $rows[] = sprintf(
                    '<tr>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;"></td>
                        <td style="padding:6px 8px;border:1px solid #ddd;"margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;></td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;"></td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    </tr>',
                    $this->esc($subscriptionId),
                    $this->esc($startDateStr),
                    $this->esc($planName),
                    $this->esc($nextPaymentDate),
                    $this->esc($cancelBefore),
                    $this->esc($formattedSubscriptionTotal . ' + tax/shipping if applicable')
                );
                continue;
            }

            // At least one item exists: render first row with subscription cells merged via rowspan
            $rowspanAttr = ' rowspan="' . (int)$itemCount . '"';

            // First product row
            $first = $items[0];
            $firstName     = (string)($first['name'] ?? '');
            $firstQty      = (float)($first['qty'] ?? 0);
            $firstUnit     = (float)($first['regular_price'] ?? 0);
            $formattedUnit = $this->priceHelper->currency($firstUnit, true, false);

            $rows[] = sprintf(
                '<tr>
                    <td%s style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    <td%s style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    <td%s style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    <td%s style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    <td%s style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>

                    <td%s style="padding:6px 8px;border:1px solid #ddd; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                </tr>',
                $rowspanAttr, $this->esc($subscriptionId),               // Subscription #
                $rowspanAttr, $this->esc($startDateStr),                 // Start Date
                $rowspanAttr, $this->esc($planName),                     // Subscription Plan
                $rowspanAttr, $this->esc($nextPaymentDate),              // Next Occurrence Date
                $rowspanAttr, $this->esc($cancelBefore),                 // Cancel Before

                $this->esc($firstName),                                  // Product(s) on Subscription
                $this->esc($formattedUnit),                              // Price per Product
                $this->esc(rtrim(rtrim((string)$firstQty, '0'), '.')),   // Qty

                $rowspanAttr, $this->esc($formattedSubscriptionTotal . ' + tax/shipping if applicable') // Total price (merged)
            );

            // Remaining product rows: only product-level cells
            for ($i = 1; $i < $itemCount; $i++) {
                $it   = $items[$i];
                $name = (string)($it['name'] ?? '');
                $qty  = (float)($it['qty'] ?? 0);
                $unit = (float)($it['regular_price'] ?? 0);
                $formattedUnit = $this->priceHelper->currency($unit, true, false);

                $rows[] = sprintf(
                    '<tr>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                        <td style="padding:6px 8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">%s</td>
                    </tr>',
                    $this->esc($name),                                  // Product(s) on Subscription
                    $this->esc($formattedUnit),                         // Price per Product
                    $this->esc(rtrim(rtrim((string)$qty, '0'), '.'))    // Qty
                );
            }
        }

        if (!$rows) {
            return '';
        }

        // Table header labels to match your screenshot
        return sprintf(
            '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%%;font-family:Arial,sans-serif;">
                <thead style="background: aliceblue;">
                    <tr>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Subscription#</th>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Start Date</th>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Subscription Plan</th>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Next Occurrence Date</th>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Cancel Before</th>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Product(s) on Subscription</th>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Price per Product</th>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Qty</th>
                        <th style="text-align:center;padding:8px;border:1px solid #ddd;margin-top: 0; margin-bottom: 25px; font-family: Georgia,serif,sans-serif,Open Sans,Helvetica Neue,Helvetica,Arial; font-size: 11pt;">Total Price of Subscription</th>
                    </tr>
                </thead>
                <tbody>%s</tbody>
            </table>',
            implode('', $rows)
        );
    }

    /**
     * Formats a date using the store's locale/timezone into a long, human-friendly style.
     * Example: "December 1, 2025"
     */
    private function formatDateLong(\DateTimeImmutable $dt, int $storeId): string
    {
        return $this->tz->formatDateTime(
            $dt,
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE,
            null,
            null,
            'MMMM d, yyyy'
        );
    }

    /**
     * Derives a display name from the customer's email or falls back to the email.
     * Examples:
     *   "jane.doe@example.com" -> "Jane doe"
     *   "john_doe@example.com" -> "John doe"
     */
    private function deriveCustomerName(string $email, int $customerId, int $storeId): string
    {
        $local = strstr($email, '@', true);
        if ($local) {
            // Replace ., _, - with spaces, collapse multiple spaces, ucfirst
            $pretty = preg_replace('/[._-]+/', ' ', $local);
            $pretty = preg_replace('/\s+/', ' ', (string)$pretty);
            return ucfirst(trim((string)$pretty));
        }
        return $email;
    }

    /**
     * HTML escape helper.
     */
    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
