<?php

namespace Abbott\Pndp\ViewModel;

use Magento\Sales\Block\Adminhtml\Order\Create\Items as Items;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\ResourceModel\Product;

class CheckPndp implements ArgumentInterface
{
    /**
     * @var Items
     */
    public $items;

    /**
     * construct function
     *
     * @param Items $items
     */
    public function __construct(Items $items)
    {
        $this->items = $items;
    }

    /**
     * Check Pndp Item function
     *
     * @return string|null
     */
    public function checkPndpItem(): ?string
    {
        $pndpMsg = "";
        $quoteData = $this->items->getQuote();
        $items = $quoteData->getAllItems();
        foreach ($items as $item) {
            $product = $this->items->getProduct($item);
            /** @var Product $resource */
            $resource = $product->getResource();
            if ($resource->getAttributeRawValue(
                $product->getId(),
                'is_pndp',
                $this->items->getStoreId()
            ) && ($product->getData('metabolic_state'))) {
                $pndpMsg = $product->getData('metabolic_state');
                break;
            }
        }
        return $pndpMsg;
    }
}
