<?php

namespace Abbott\Sales\Plugin;

use Magento\Framework\DataObject;

/**
 * Abstract block for display sales (quote/order/invoice etc.) items
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class AbstractItems
{

    /**
     * Get item row html
     *
     * @param DataObject $item
     * @param $result
     * @return void
     */
    public function beforeGetItemHtml(DataObject $item, $result)
    {
        if ($item instanceof \Magento\Sales\Block\Order\Items) {
            $description = strip_tags($result->getDescription() ?? "");
            $result->setDescription($description);
        }
    }
}
