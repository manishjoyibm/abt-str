<?php
declare(strict_types=1);

namespace Abbott\OrderManagement\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class CancelVisibility
 *
 * Determines whether the Cancel button should be displayed
 * for a given order based on admin configuration:
 *   - Global enable/disable toggle
 *   - List of allowed order statuses
 *
 * This class is used by plugins and UI blocks to ensure
 * consistent cancel-button behavior across storefront and admin.
 */
class CancelVisibility
{
    /**
     * XML Path for enabling/disabling cancel button visibility.
     *
     * @var string
     */
    private const XML_PATH_ENABLED = 'cancel_order_setting/cancel_order/enabled';

    /**
     * XML Path for allowed statuses to show cancel button.
     *
     * Comma‑separated list of status codes.
     * Example: "hold,suspected_fraud"
     *
     * @var string
     */
    private const XML_PATH_ALLOWED_STATUSES = 'cancel_order_setting/cancel_order/allowed_statuses';

    /**
     * @param ScopeConfigInterface $scopeConfig Magento store configuration reader.
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    /**
     * Determine whether the cancel button should be visible
     * for a given order.
     *
     * Logic:
     *  - Check if module is enabled.
     *  - Retrieve allowed statuses list.
     *  - Return TRUE only if the order's status exists in the allowed list.
     *
     * @param OrderInterface $order The order being evaluated.
     *
     * @return bool TRUE if cancel button should be shown, FALSE otherwise.
     */
    public function isCancelVisible(OrderInterface $order): bool
    {
        // Check if cancel visibility feature is enabled
        $enabled = (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        if (!$enabled) {
            return false;
        }

        // List of allowed statuses (comma separated)
        $allowed = (string) $this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_STATUSES,
            ScopeInterface::SCOPE_STORE
        );

        $allowedStatuses = array_filter(
            array_map('trim', explode(',', $allowed))
        );

        if (empty($allowedStatuses)) {
            return false;
        }

        // Compare current order status with allowed statuses
        return in_array($order->getStatus(), $allowedStatuses, true);
    }
}
