<?php
namespace Abbott\StockManagement\Plugin\CatalogInventory\Model\Source;

class Backorders
{
    public function afterToOptionArray(
        \Magento\CatalogInventory\Model\Source\Backorders $subject,
        $result
    ) {

        $result = array_merge($result, [
            ['value' => '4', 'label' => __('No backorders,Except subscriptions')]
        ]);

        return $result;
    }
}
