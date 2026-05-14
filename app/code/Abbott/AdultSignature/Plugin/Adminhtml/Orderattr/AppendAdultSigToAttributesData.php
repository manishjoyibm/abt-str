<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Plugin\Adminhtml\Orderattr;

use Amasty\Orderattr\Block\Adminhtml\Order\View\Attributes as Subject;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Phrase;

class AppendAdultSigToAttributesData
{
    /**
     * Safely normalize any value (int/bool/string/Phrase) to int(0|1)
     */
    private function toInt(mixed $value): int
    {
        if ($value instanceof Phrase) {
            $value = (string)$value->render();
        }
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_numeric($value)) {
            return (int)$value;
        }
        $v = strtolower(trim((string)$value));
        return in_array($v, ['1','true','yes','y'], true) ? 1 : 0;
    }

    /**
     * Append Adult Signature rows to Amasty attributes list.
     *
     * @param Subject $subject
     * @param array   $result  Array of ['label' => ..., 'value' => ...]
     * @return array
     */
    public function afterGetOrderAttributesData(Subject $subject, array $result): array
    {
        // Only on Order view page (not shipments/invoices)
        if (method_exists($subject, 'isOrderViewPage') && !$subject->isOrderViewPage()) {
            return $result; // uses Amasty API shown in the class
        }

        // Get current Order (Amasty class fetches it from parent block)
        $order = null;
        $parent = $subject->getParentBlock(); // public in Magento base blocks
        if ($parent && method_exists($parent, 'getOrder')) {
            /** @var OrderInterface|null $order */
            $order = $parent->getOrder();
        }
        if (!$order) {
            return $result;
        }

        $required = $this->toInt($order->getData('adult_signature_required')) === 1;
        $accepted = $this->toInt($order->getData('adult_signature_accepted')) === 1;

        // Row 1: Adult Signature (concise overall status)

        $result[] = [
            'label' => __('Adult Signature Required'),
            'value' => $required ? __('Yes') : __('No')
        ];

        return $result;
    }
}