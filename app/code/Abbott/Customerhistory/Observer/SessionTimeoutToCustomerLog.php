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
 * Observer: SessionTimeoutToCustomerLog
 *
 * Detects customer session timeouts and writes an audit stamp to `customer_log`.
 *
 * Strategy:
 *  - If the explicit manual-logout cookie is present, delete it and exit (manual flow).
 *  - Skip processing on the explicit logout route (`customer_account_logout`).
 *  - Only stamp "Session Logout" when the customer is NOT logged in and we have a valid ID.
 *
 * Notes:
 *  - This stamps `last_logout_at` with the computed expiry (now + lifetime).
 *    If your audit policy requires the detection time instead, use the current time.
 */
class SessionTimeoutToCustomerLog implements ObserverInterface
{
    private CustomerSession $customerSession;
    private DateTime $utcClock;
    private Logger $customerLogger;
    private LoggerInterface $logger;
    private Data $helper;

    public function __construct(
        CustomerSession   $customerSession,
        DateTime          $utcClock,
        Logger            $customerLogger,
        LoggerInterface   $logger,
        Data              $helper
    ) {
        $this->customerSession = $customerSession;
        $this->utcClock        = $utcClock;
        $this->customerLogger  = $customerLogger;
        $this->logger          = $logger;
        $this->helper          = $helper;
    }

    /**
     * Runs on `controller_action_predispatch` (global).
     * We skip when the action is `customer_account_logout`, and we avoid
     * stamping if the manual-logout cookie is present.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            // Only stamp timeout if the customer is NOT logged in
            if (!$this->customerSession->isLoggedIn()) {
                return; // logged-in request: not a timeout scenario
            }
             // Check if feature and session logout tracking are enabled
            if (!$this->helper->isEnabled() || !$this->helper->isSessionEnabled()) {
                return; // Do nothing if disabled
            }
            // Get current controller action & full action name
            $action  = $observer->getEvent()->getControllerAction();
            $request = $action ? $action->getRequest() : null;
            $fullActionName = $request ? (string)$request->getFullActionName() : ''; 
            $cookieValue = $this->helper->getManualLogoutCookie();

            // Never run during the explicit logout route customer_account_logoutSuccessmanual_logout
            if ($fullActionName === 'customer_account_logoutSuccess' || $cookieValue) {
                 // 1) Manual logout cookie present? Delete and exit (manual case)
                if ($cookieValue) {
                    $this->helper->deleteManualLogoutCookie();
                    return; // exit without stamping a session timeout
                };
                return;
            }

            // We need a valid customer ID to stamp logs
             $customerId = (int)($this->customerSession->getCustomerId() ?? 0); 
            if ($customerId <= 0) {
                return; // no customer context; nothing to stamp
            }

            // Compute expiry and write audit
            $customerLifetime = $this->helper->getCustomerLifetime();     // seconds
            $nowUtcTs         = (int)$this->utcClock->gmtTimestamp();     // current UTC
            $expiresUtcTs     = $nowUtcTs + $customerLifetime;            // absolute expiry
            $expiresUtcYmdHis = gmdate('Y-m-d H:i:s', $expiresUtcTs);     // MySQL datetime

            // Stamp `last_logout_at` as the computed expiry
            $this->customerLogger->log($customerId, ['last_logout_at' => $expiresUtcYmdHis]);

            // Stamp source
            $this->customerLogger->log($customerId, ['logout_source' => 'Session Logout']);
        } catch (\Throwable $e) {
            // Do not break request flow due to logging; record and continue.
            $this->logger->error(sprintf(
                'SessionTimeoutToCustomerLog error: %s',
                $e->getMessage()
            ));
        }
    }
}