<?php

namespace Abbott\Analytics\Block;

use Magento\Store\Model\StoreManagerInterface;

class RemoveItem extends \Magento\Framework\View\Element\Template
{
    public $productRepository;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    public $categoryRepository;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;
    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
    ) {
        $this->serializer = $serializer;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getRemovedItem()
    {
        $productData = [];
        $items = $this->checkoutSession->getRemovedItems() ? $this->checkoutSession->getRemovedItems() : [] ;
        foreach ($items as $item) {
            if ($item["id"]) {
                $productData[] = $this->createProductData($item);
            }
        }
        if($productData) {
        $productData[] = ['currencyCode' => $this->getStoreCurrencyCode()];
        }
        $this->checkoutSession->unsRemovedItems();
        return $this->serializer->serialize($productData);
    }
    private function createProductData($item)
    {
            $product = $this->getProductById($item["id"]);
         $variants = [
             $product->getData('case_of_product'),
             $product->getData('product_flavor'),
             $product->getData('product_form')
         ];
         $combinedVariants = $this->getCombinedVariants($variants);
            return [
                    'name'     => $product->getName(),
                    'id'       => $product->getSku(),
                    'price'    => number_format($item['price'], 2),
                    'brand'         => $product->getData('brand'),
                    'quantity' => $item['qty'],
                    'variant'  => $combinedVariants
            ];
    }
    private function getProductById($id)
    {
            return $this->productRepository->getById($id);
    }
    public function getCombinedData($attribute, $delimiter)
    {
        return $attribute && $attribute != "null" ? $attribute.''.$delimiter : '';
    }
    public function getCombinedVariants($variants)
    {
        $names = '';
        foreach ($variants as $variant) {
            $names = $names.''.$this->getCombinedData($variant, ' | ');
        }
        return $names ? rtrim($names, ' | ') : 'NA';
    }
    /**
     * Get store currency code for page tracking javascript code
     *
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }
}
