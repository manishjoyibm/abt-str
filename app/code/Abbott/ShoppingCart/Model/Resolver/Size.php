<?php

namespace Abbott\ShoppingCart\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class Size implements ResolverInterface
{
    /**
     * @var MetadataPool
     */
    public $metadataPool;

    /**
     * Construct
     *
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * Resolver
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return false|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $item = $value['model'];
        $optionText = false;
        $attr = $item->getProduct()->getResource()->getAttribute('size');
        $attrId =  $item->getProduct()
            ->getResource()
            ->getAttributeRawValue($item->getProduct()->getId(), 'size', $item->getStoreId());
        if ($attrId && $attr->usesSource()) {
            $optionText = $attr->getSource()->getOptionText($attrId);
        }
        return $optionText;
    }
}
