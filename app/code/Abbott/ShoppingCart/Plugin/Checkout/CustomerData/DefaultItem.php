<?php

namespace Abbott\ShoppingCart\Plugin\Checkout\CustomerData;

use Magento\Catalog\Api\ProductRepositoryInterface;

class DefaultItem
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $productRepo;
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepo = $productRepository;
    }
    
    public function aroundGetItemData(
        \Magento\Checkout\CustomerData\AbstractItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item $item
    ) {
        $data = $proceed($item);
        $result['size_or_weight'] = $item->getProduct()->getData('size_or_weight');
        return array_merge(
            $result,
            $data
        );
    }
}
