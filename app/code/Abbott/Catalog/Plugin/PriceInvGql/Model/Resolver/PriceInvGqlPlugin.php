<?php


namespace Abbott\Catalog\Plugin\PriceInvGql\Model\Resolver;

use Abbott\PriceInvGql\Model\Resolver\PriceInvGql;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class PriceInvGqlPlugin
{
    /**
     * @var \Abbott\Catalog\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * PriceInvGqlPlugin constructor.
     * @param \Abbott\Catalog\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Abbott\Catalog\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->helper = $helper;
        $this->productFactory = $productFactory;
    }

    /**
     * @param PriceInvGql $subject
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     */
    public function afterResolve(
        PriceInvGql $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($this->helper->isDisableSaleEnabled($context->getExtensionAttributes()->getStore()->getId()) &&
            isset($result["products"]) && is_array($result["products"]) && !empty($result["products"])
        ) {
            foreach ($result["products"] as &$productData) {
                $product = $this->productFactory->create();
                $product->load($product->getIdBySku($productData["product_sku"]));
                if ($product->getDisableSale()) {
                    $productData["qty"] = 0;
                    $productData["is_in_stock"] = false;
                }
            }
        }
        return $result;
    }
}
