<?php

namespace Abbott\ShoppingCart\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class AemUrl implements ResolverInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    public $metadataPool;
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

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
        return $item->getProduct()
            ->getResource()
            ->getAttributeRawValue($item->getProduct()->getId(), 'aem_url', $item->getStoreId());
    }
}
