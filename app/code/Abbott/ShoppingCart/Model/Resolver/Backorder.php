<?php

namespace Abbott\ShoppingCart\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class Backorder implements ResolverInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    public $metadataPool;
    public const BACKORDER = 'backorder';
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * Construct
     *
     * @param MetadataPool $metadataPool
     * @param StockRegistryInterface $stockRegistry
     * @param StockStatusRepositoryInterface $stockStatusRepository
     */
    public function __construct(
        MetadataPool $metadataPool,
        StockRegistryInterface $stockRegistry,
        StockStatusRepositoryInterface $stockStatusRepository
    ) {
        $this->metadataPool = $metadataPool;
        $this->stockRegistry = $stockRegistry;
        $this->stockStatusRepository = $stockStatusRepository;
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
        $product = $value['model']->getProduct();
        $stockdata['qty'] = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getQty();
        $stockdata[self::BACKORDER] = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getBackorders();
        $stockStatus = $this->stockStatusRepository->get($product->getId());
        $productStockStatus = (int)$stockStatus->getStockStatus();
        if (($stockdata[self::BACKORDER] == 1 || $stockdata[self::BACKORDER] == 2) &&
            $productStockStatus == 1 && $stockdata['qty'] <= 0) {
            $stockdata['status'] = 1;
        } else {
            $stockdata['status'] = 0;
        }
        return $stockdata['status'];
    }
}
