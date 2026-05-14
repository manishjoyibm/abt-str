<?php

namespace Abbott\Checkout\Model;

use Amasty\Orderattr\Model\Indexer\Conditions\ProductProcessor;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;

class ProductAvailabilityAttribute extends AbstractAttribute
{
    /**
     * @var EavSetup
     */
    protected EavSetup $eavSetup;

    /**
     * @var EntityType
     */
    protected EntityType $entityType;

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resourceConnection;

    /**
     * @param LoggerInterface $logger
     * @param StockRegistryInterface $stockRegistry
     * @param EavSetup $eavSetup
     * @param EntityType $entityType
     * @param ResourceConnection $resourceConnection
     * @param ProductProcessor $productProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        StockRegistryInterface $stockRegistry,
        EavSetup $eavSetup,
        EntityType $entityType,
        ResourceConnection $resourceConnection,
        ProductProcessor $productProcessor
    ) {
        $this->logger = $logger;
        $this->stockRegistry = $stockRegistry;
        $this->eavSetup = $eavSetup;
        $this->entityType = $entityType;
        $this->resourceConnection = $resourceConnection;
        $this->productProcessor = $productProcessor;
    }

    /**
     * Update product_availability Attribute
     *
     * @param Product $product
     * @param mixed $storeId
     * @return void
     */
    public function updateProductAvailability(Product $product, $storeId): void
    {
        try {
            $attribute = $this->loadAvailabilityAttribute();
            if (!$attribute) {
                return;
            }
            $attributeId = $attribute['attribute_id'];
            $optionId = $this->determineAvailabilityOption($product);
            $this->saveAttributeValue($product, $storeId, $attributeId, $optionId);
            $this->productProcessor->reindexRow((int)$product->getId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Load product_availability attribute definition.
     *
     * @return array|null
     */
    private function loadAvailabilityAttribute(): ?array
    {
        $entityTypeId = $this->entityType->loadByCode('catalog_product')->getId();
        return $this->eavSetup->getAttribute($entityTypeId, 'product_availability');
    }

    /**
     * Determine the product availability option ID.
     *
     * @param Product $product
     * @return int|string|null
     */
    private function determineAvailabilityOption($product): int|string|null
    {
        if (!$this->isProductAvailable($product)) {
            return null;
        }
        $attribute = $product->getResource()->getAttribute('product_availability');
        if (!$attribute) {
            return null;
        }
        foreach ($attribute->getOptions() as $option) {
            if (!empty($option->getValue())) {
                return $attribute->getSource()->getOptionId($option->getValue());
            }
        }
        return null;
    }

    /**
    * Check if product is considered "in stock" based on qty, backorders, and stock status.
    *
    * @param Product $product
    * @return bool
    */
    private function isProductAvailable($product): bool
    {
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $qty = (int)$stockItem->getQty();
        $threshold = (int)$product->getThreshold();
        $backorders = (int)$stockItem->getBackorders();
        $inStock = ($qty > 0);
        if ($backorders === 4 && $qty <= $threshold) {
            $inStock = false;
        }
        $stockStatus = $product->getQuantityAndStockStatus();
        return is_array($stockStatus)
            && !empty($stockStatus['is_in_stock'])
            && $stockStatus['is_in_stock'] > 0
            && $inStock;
    }

    /**
     * Save attribute value in catalog_product_entity_varchar table.
     *
     * @param Product $product
     * @param int $storeId
     * @param int $attributeId
     * @param int|null $optionId
     * @return void
     */
    private function saveAttributeValue($product, int $storeId, int $attributeId, ?int $optionId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate(
            'catalog_product_entity_varchar',
            [
                'row_id'       => $product->getRowId(),
                'store_id'     => $storeId,
                'attribute_id' => $attributeId,
                'value'        => $optionId
            ]
        );
    }
}
