<?php


namespace Abbott\StockManagement\Plugin\Model\Resolver;

use Abbott\PriceInvGql\Model\Resolver\PriceInvGql;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class PriceInvGqlPlugin
 */
class StockPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    const BACKORDER = 4;
    /**
     * @var \Abbott\StockManagement\Helper\Data
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
        \Abbott\StockManagement\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
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
        if (isset($result["products"]) && is_array($result["products"]) && !empty($result["products"])) {
            foreach ($result["products"] as &$productData) {
                $product = $this->productFactory->create();
                $product->load($product->getIdBySku($productData["product_sku"]));
                if ($this->helper->getConfigValue() && $this->helper->checkStock($product) == self::BACKORDER) {
                    $threshold = $product->getData('threshold');
                    if ($product->getData()['quantity_and_stock_status']['is_in_stock']
                        && $product->getData()['quantity_and_stock_status']['qty'] <= $threshold) {
                        $productData["qty"] = 0;
                        $productData["is_in_stock"] = false;
                    }
                }
            }
        }
        return $result;
    }
}
