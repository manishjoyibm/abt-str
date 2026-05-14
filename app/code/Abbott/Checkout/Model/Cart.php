<?php


namespace Abbott\Checkout\Model;


use Magento\Framework\Exception\NoSuchEntityException;

class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * Convert order item to quote item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param true|null $qtyFlag if is null set product qty like in order
     * @return \Magento\Checkout\Model\Cart
     */
    public function addOrderItem($orderItem, $qtyFlag = null)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /**
                 * We need to reload product in this place, because products
                 * with the same id may have different sets of order attributes.
                 */
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
                if ($orderItem->getOrderId() !== null) {
                    //reorder existing order
                    $product->setSkipCheckRequiredOption(true);
                }
            } catch (NoSuchEntityException $e) {
                return $this;
            }
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Framework\DataObject($info);
            if ($qtyFlag === null) {
                $info->setQty($orderItem->getQtyOrdered());
            } else {
                $info->setQty(1);
            }
            $productOptions = $orderItem->getProductOptions();
            if ($productOptions !== null && !empty($productOptions['options'])) {
                $formattedOptions = [];

                foreach ($productOptions['options'] as $option) {
                    if (isset($option['option_id']) && $option['option_value']) {
                        $formattedOptions[$option['option_id']] = $option['option_value'];
                    }
                }
                $info->setData('options', $formattedOptions);
            }
            $this->addProduct($product, $info);
        }
        return $this;
    }
}
