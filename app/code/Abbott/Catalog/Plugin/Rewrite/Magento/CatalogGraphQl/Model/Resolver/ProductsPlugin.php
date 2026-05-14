<?php


namespace Abbott\Catalog\Plugin\Rewrite\Magento\CatalogGraphQl\Model\Resolver;

use Abbott\SmileSearch\Rewrite\Magento\CatalogGraphQl\Model\Resolver\Products;
use GraphQL\Language\AST\SelectionNode;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class ProductsPlugin
 * @package Abbott\Catalog\Plugin\Rewrite\Magento\CatalogGraphQl\Model\Resolver
 */
class ProductsPlugin
{
    /**
     * @var \Abbott\Catalog\Helper\Data
     */
    private $helper;

    /**
     * ProductsPlugin constructor.
     * @param \Abbott\Catalog\Helper\Data $helper
     */
    public function __construct(\Abbott\Catalog\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Products $subject
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function beforeResolve(
        Products $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (
            $this->helper->isDisableSaleEnabled($context->getExtensionAttributes()->getStore()->getId()) &&
            $this->helper->isDisableSaleSortOrderEnabled($context->getExtensionAttributes()->getStore()->getId())
        ) {
            if (!isset($args['sort'])) {
                $args['sort'] = [];
            }
            $args['sort']['disable_sale'] = 'ASC';
        }

        return [$field, $context, $info, $value, $args];
    }
}
