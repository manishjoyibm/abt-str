<?php
declare(strict_types=1);

namespace Abbott\Customerhistory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Logger;
use Psr\Log\LoggerInterface;
use Abbott\Customerhistory\Helper\Data;

/**
 * Observer: ExplicitLogoutToCustomerLog
 *
 * Handles explicit (manual) customer logout and writes an audit trail to `customer_log`.
 *
 * Behavior:
 *  - Sets a short-lived secure, HttpOnly cookie to mark manual logout (via helper).
 *  - Logs `logout_source = 'Manual Logout'`.
 *  - (Optional) Also stamps `last_logout_at` with the current UTC time for consistency.
 *
 * @category Abbott
 * @package  Abbott_Customerhistory
 */
class ExplicitLogoutToCustomerLog implements ObserverInterface
{
    /** @var CustomerSession */
    private CustomerSession $customerSession;

    /** @var DateTime */
    private DateTime $utcClock;

    /**
     * Customer logger (business-specific). Must expose ->log(int $customerId, array $data): void
     *
     * @var object
     */
    private $customerLogger;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /** @var Data */
    private Data $helper;

    /**
     * @param CustomerSession  $customerSession
     * @param DateTime         $utcClock
     * @param Logger           $customerLogger  Business logger exposing ->log($customerId, array $data)
     * @param LoggerInterface  $logger
     * @param Data             $helper
     */
    public function __construct(
        CustomerSession  $customerSession,
        DateTime         $utcClock,
        Logger           $customerLogger,
        LoggerInterface  $logger,
        Data             $helper
    ) {
        $this->customerSession = $customerSession;
        $this->utcClock        = $utcClock;
        $this->customerLogger  = $customerLogger;
        $this->logger          = $logger;
        $this->helper          = $helper;
    }

    /**
     * Execute observer on explicit logout event.
     *
     * Expected event payload: customer model available at $observer->getEvent()->getCustomer()
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $event    = $observer->getEvent();
            $customer = $event->getCustomer();

            // Defensive: ensure we have a customer instance and an ID.
            if (!$customer || !method_exists($customer, 'getId')) {
                return;
            }

            $customerId = (int)$customer->getId();
            if ($customerId <= 0) {
                return;
            }

            // Mark manual logout via secure, HttpOnly cookie (short-lived).
            $this->helper->setManualLogoutCookie();


            // Source marker for audit purposes.
            $this->customerLogger->log($customerId, ['logout_source' => 'Manual Logout']);
        } catch (\Throwable $e) {
            // Do not break the request due to logging issues; record and continue.
            $this->logger->error(sprintf('ExplicitLogoutToCustomerLog error: %s',$e->getMessage()));
        }
    }
}