<?php

namespace Abbott\ShippingRestriction\Model\Config\Source;

class Stores
{
    public $options;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * ToOptionArray
     *
     * @return array
     */
    public function toOptionArray()
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $this->options[] = [
                'value' => $store->getId(),
                'label' => $store->getName(),
            ];
        }
        return $this->options;
    }
}
