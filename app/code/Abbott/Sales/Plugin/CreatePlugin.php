<?php


namespace Abbott\Sales\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order\Item;

/**
 * Class Create
 */
class CreatePlugin
{
    const SUBCRIPTION_ADD_TO_CART_WARNING = "Cannot add recently ordered subscription to the cart.";

/**
 * Initialize creation data from existing order Item
 *
 * @param Create $subject
 * @param Item $orderItem
 * @param int|null $qty
 * @return array
 * @throws CouldNotSaveException
 */
    public function beforeInitFromOrderItem(
        Create $subject,
        Item $orderItem,
        $qty = null
    ) {
        $productOptions = $orderItem->getProductOptions();

        if ($productOptions !== null && !empty($productOptions['options'])) {
            foreach ($productOptions['options'] as $option) {
                if (!array_key_exists("option_type", $option)) {
                    throw new CouldNotSaveException(__(self::SUBCRIPTION_ADD_TO_CART_WARNING));
                }
            }
        }

        return [$orderItem, $qty];
    }
}
