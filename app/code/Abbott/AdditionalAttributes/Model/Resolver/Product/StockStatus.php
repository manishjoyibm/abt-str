<?php

declare(strict_types=1);

namespace Abbott\AdditionalAttributes\Model\Resolver\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * @inheritdoc
 */
class StockStatus implements ResolverInterface
{
    const MODEL = 'model';
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        StockStatusRepositoryInterface $stockStatusRepository
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->stockStatusRepository = $stockStatusRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!array_key_exists(self::MODEL, $value) || !$value[self::MODEL] instanceof ProductInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /* @var $product ProductInterface */
        $product = $value[self::MODEL];
        $stockStatus = $this->stockStatusRepository->get($product->getId());
        $productStockStatus = (int)$stockStatus->getStockStatus();
        return $productStockStatus === 1 ? 'IN_STOCK' : 'OUT_OF_STOCK';
    }
}
