<?php
declare(strict_types=1);

namespace Abbott\OrderManagement\Plugin;

use Magento\Sales\Model\Order;
use Abbott\OrderManagement\ViewModel\CancelVisibility;

/**
 * Class OrderCanCancelPlugin
 *
 * Plugin for Magento\Sales\Model\Order::canCancel()
 *
 * This plugin allows overriding Magento's default behavior for determining
 * whether an order can be cancelled. If the core logic returns FALSE but the
 * order status is allowed by configuration (as determined by the
 * CancelVisibility ViewModel), this plugin forces canCancel() to TRUE.
 *
 * This ensures consistent behavior between:
 *   - Backend Admin cancel button visibility
 *   - Frontend customer cancel button visibility
 *   - Actual business logic for cancellation permission
 */
class OrderCanCancelPlugin
{
    /**
     * @var CancelVisibility
     *
     * ViewModel responsible for evaluating cancel-button visibility rules.
     * Injected via Magento's DI container.
     */
    public function __construct(
        private readonly CancelVisibility $cancelVisibility
    ) {}

    /**
     * After-plugin for Order::canCancel()
     *
     * If the core result is FALSE, this method checks whether the admin
     * configuration allows cancellation for the order’s current status.
     *
     * @param Order $subject  The order instance being evaluated.
     * @param bool  $result   Original result returned by Order::canCancel().
     *
     * @return bool           Updated result based on configuration rules.
     */
    public function afterCanCancel(Order $subject, bool $result): bool
    {
        /**
         * If Magento core says "cannot cancel", but configuration allows
         * cancellation for this order status, force TRUE.
         */
        if ($result === false && $this->cancelVisibility->isCancelVisible($subject)) {
            return true;
        }

        // Otherwise keep original behavior.
        return $result;
    }
}
