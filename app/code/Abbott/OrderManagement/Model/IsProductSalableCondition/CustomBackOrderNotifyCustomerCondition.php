<?php

declare(strict_types=1);

namespace Abbott\OrderManagement\Model\IsProductSalableCondition;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderNotifyCustomerCondition;

/**
 * @inheritdoc
 */
class CustomBackOrderNotifyCustomerCondition extends BackOrderNotifyCustomerCondition implements
    IsProductSalableForRequestedQtyInterface
{
    const ERRORS = 'errors';

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     *
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetStockItemDataInterface $getStockItemData
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetStockItemDataInterface $getStockItemData,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductRepository $productRepository
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getStockItemData = $getStockItemData;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $product = $this->productRepository->get($sku);
        if ($stockItemConfiguration->getBackorders() === StockItemConfigurationInterface::BACKORDERS_YES_NOTIFY) {
            $stockItemData = $this->getStockItemData->execute($sku, $stockId);
            if (null === $stockItemData) {
                return $this->productSalableResultFactory->create([self::ERRORS => []]);
            }

            $backOrderQty = $requestedQty - $stockItemData[GetStockItemDataInterface::QUANTITY];
            if ($backOrderQty > 0) {
                $errors = [
                    $this->productSalabilityErrorFactory->create([
                            'code' => 'back_order-not-enough',
                            'message' => __(
                                'We don\'t have as many "%1" as you requested, '
                                    . 'but we\'ll back order the remaining %2.',
                                $product->getName(),
                                $backOrderQty * 1
                            )])
                ];
                return $this->productSalableResultFactory->create([self::ERRORS => $errors]);
            }
        }

        return $this->productSalableResultFactory->create([self::ERRORS => []]);
    }
}
