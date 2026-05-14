<?php

declare(strict_types=1);
namespace Abbott\StockManagement\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Math\Division as MathDivision;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\Framework\Phrase;

class IsCorrectQtyCondition extends \Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsCorrectQtyCondition
{

    public const BACKORDER = 4;
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @var MathDivision
     */
    private $mathDivision;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    private $productRepository;

    /**
     * @var \Abbott\StockManagement\Helper\Data
     */
    private $helper;
    /**
     * IsCorrectQtyCondition constructor.
     *
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param StockConfigurationInterface $configuration
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param GetStockItemDataInterface $getStockItemData
     * @param MathDivision $mathDivision
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     */
    public function __construct(
        \Abbott\StockManagement\Helper\Data $helper,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        StockConfigurationInterface $configuration,
        GetReservationsQuantityInterface $getReservationsQuantity,
        GetStockItemDataInterface $getStockItemData,
        MathDivision $mathDivision,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->configuration = $configuration;
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->mathDivision = $mathDivision;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->helper = $helper;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->productRepository = $productRepository;
        parent::__construct(
            $getStockItemConfiguration,
            $configuration,
            $getReservationsQuantity,
            $getStockItemData,
            $mathDivision,
            $productSalabilityErrorFactory,
            $productSalableResultFactory
        );
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);

        if ($this->isMinSaleQuantityCheckFailed($stockItemConfiguration, $requestedQty)) {
            return $this->createErrorResult(
                'is_correct_qty-min_sale_qty',
                __(
                    'The fewest you may purchase is %1',
                    $stockItemConfiguration->getMinSaleQty()
                )
            );
        }

        $result = $this->isMaxSaleQuantityCheckFailed($stockItemConfiguration, $requestedQty, $sku);

        if ($result['status']) {
            return $this->createErrorResult(
                'is_correct_qty-max_sale_qty',
                __($result['message'])
            );
        }

        if ($this->isQuantityIncrementCheckFailed($stockItemConfiguration, $requestedQty)) {
            return $this->createErrorResult(
                'is_correct_qty-qty_increment',
                __(
                    'You can buy this product only in quantities of %1 at a time.',
                    $stockItemConfiguration->getQtyIncrements()
                )
            );
        }

        if ($this->isDecimalQtyCheckFailed($stockItemConfiguration, $requestedQty)) {
            return $this->createErrorResult(
                'is_correct_qty-is_qty_decimal',
                __('You cannot use decimal quantity for this product.')
            );
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }

    /**
     * Create Error Result Object
     *
     * @param string $code
     * @param Phrase $message
     * @return ProductSalableResultInterface
     */
    private function createErrorResult(string $code, Phrase $message): ProductSalableResultInterface
    {
        $errors = [
            $this->productSalabilityErrorFactory->create([
                'code' => $code,
                'message' => $message
            ])
        ];
        return $this->productSalableResultFactory->create(['errors' => $errors]);
    }

    /**
     * Check if decimal quantity is valid
     *
     * @param StockItemConfigurationInterface $stockItemConfiguration
     * @param float $requestedQty
     * @return bool
     */
    private function isDecimalQtyCheckFailed(
        StockItemConfigurationInterface $stockItemConfiguration,
        float $requestedQty
    ): bool {
        return (!$stockItemConfiguration->isQtyDecimal() && (floor($requestedQty) !== $requestedQty));
    }

    /**
     * Check if min sale condition is satisfied
     *
     * @param StockItemConfigurationInterface $stockItemConfiguration
     * @param float $requestedQty
     * @return bool
     */
    private function isMinSaleQuantityCheckFailed(
        StockItemConfigurationInterface $stockItemConfiguration,
        float $requestedQty
    ): bool {
        // Minimum Qty Allowed in Shopping Cart
        if ($stockItemConfiguration->getMinSaleQty() && $requestedQty < $stockItemConfiguration->getMinSaleQty()) {
            return true;
        }
        return false;
    }

    /**
     * Check if max sale condition is satisfied
     *
     * @param StockItemConfigurationInterface $stockItemConfiguration
     * @param float $requestedQty
     * @return bool
     */
    private function isMaxSaleQuantityCheckFailed(
        StockItemConfigurationInterface $stockItemConfiguration,
        float $requestedQty,
        $sku
    ) {
        $result = [];
        $result['status'] =false;
        // Maximum Qty Allowed in Shopping Cart
        if ($stockItemConfiguration->getBackorders() == self::BACKORDER) {
            $productData = $this->productRepository->get($sku);
            $maxQty = $stockItemConfiguration->getMaxSaleQty();
            $thresold = $productData->getThreshold();
            $calculatedQty =  $productData->getData()['quantity_and_stock_status']['qty'] - $thresold;
            if ($requestedQty > $calculatedQty && $calculatedQty <= $maxQty) {
                $result['status']= true;
                $result['message'] = "The requested qty exceeds the maximum qty allowed in shopping cart";
            }
            if ($calculatedQty > $maxQty && $requestedQty > $maxQty) {
                $result['status']= true;
                $result['message'] = "The most you may purchase is $maxQty.";
            }
        } else {
            if ($stockItemConfiguration->getMaxSaleQty() && $requestedQty > $stockItemConfiguration->getMaxSaleQty()) {
                $result['status']= true;
                $result['message'] = 'The requested qty exceeds the maximum qty allowed in shopping cart';
            }
        }
        return $result;
    }

    /**
     * Check if increment quantity condition is satisfied
     *
     * @param StockItemConfigurationInterface $stockItemConfiguration
     * @param float $requestedQty
     * @return bool
     */
    private function isQuantityIncrementCheckFailed(
        StockItemConfigurationInterface $stockItemConfiguration,
        float $requestedQty
    ): bool {
        // Qty Increments
        $qtyIncrements = $stockItemConfiguration->getQtyIncrements();
        if ($qtyIncrements !== (float)0 && $this->mathDivision->getExactDivision($requestedQty, $qtyIncrements) !== 0) {
            return true;
        }
        return false;
    }
}
