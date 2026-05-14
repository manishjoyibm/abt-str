<?php
declare(strict_types=1);

namespace Abbott\OrderManagement\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as StatusCollectionFactory;

/**
 * Custom source model that provides a *selective* list of order statuses
 * for a multiselect field in System Configuration.
 *
 * The selectable statuses are restricted via the $allowedCodes whitelist
 * injected through di.xml. If the whitelist is empty, it falls back to
 * returning all available order statuses.
 */
class SelectiveOrderStatus implements ArrayInterface
{
    /** @var StatusCollectionFactory */
    private StatusCollectionFactory $statusCollectionFactory;

    /**
     * @var string[] Whitelisted status codes to expose in the multiselect.
     */
    private array $allowedCodes;

    /**
     * @param StatusCollectionFactory $statusCollectionFactory
     * @param string[]                $allowedCodes (optional) whitelist injected via di.xml
     */
    public function __construct(
        StatusCollectionFactory $statusCollectionFactory,
        array $allowedCodes = []
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->allowedCodes = $allowedCodes;
    }

    /**
     * Provide options to system config multiselect.
     *
     * @return array[] Each option is ['value' => <status_code>, 'label' => <status_label>]
     */
    public function toOptionArray(): array
    {
        $collection = $this->statusCollectionFactory->create();

        $options = [];
        foreach ($collection->getItems() as $status) {
            $code  = (string) $status->getStatus(); // e.g., "hold"
            $label = (string) $status->getLabel();  // e.g., "On Hold"

            // If a whitelist is provided, skip any status not in the list.
            if (!empty($this->allowedCodes) && !in_array($code, $this->allowedCodes, true)) {
                continue;
            }

            $options[] = ['value' => $code, 'label' => $label];
        }

        // Sort by label for a cleaner UI
        usort($options, static fn($a, $b) => strcmp($a['label'], $b['label']));

        return $options;
    }
}
