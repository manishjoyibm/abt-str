<?php

declare(strict_types=1);

namespace Abbott\AdditionalAttributes\Observer;

use Exception;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;

class UpdateProductAvailability implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Construct function
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Execute function
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $availabilityOption = null;
            $product = $observer->getEvent()->getProduct();
            if (!$this->isSimpleAndEnabled($product)) {
                return;
            }
            $stockQty = $this->extractStockQty($product->getQuantityAndStockStatus());
            $threshold = (int)$product->getThreshold();
            $inStock = $this->determineInStock($product, $stockQty, $threshold);
            if (!$this->canUpdateAvailability($product, $inStock)) {
                $product->setCustomAttribute('product_availability', $availabilityOption);
            } else {
                $availabilityOption = $this->getAvailabilityOptionId($product);
            }
            $product->setCustomAttribute('product_availability', $availabilityOption);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Determine if product is Simple type and enabled.
     *
     * @param Product $product
     * @return bool
     */
    private function isSimpleAndEnabled(Product $product): bool
    {
        return $product->getTypeInstance() instanceof Simple
            && (int)$product->getStatus() === Status::STATUS_ENABLED;
    }

    /**
     * Extract stock quantity from stock data array.
     *
     * @param mixed $stockData
     * @return int
     */
    private function extractStockQty($stockData): int
    {
        return (is_array($stockData) && isset($stockData['qty']))
            ? (int)$stockData['qty']
            : 0;
    }

    /**
     * Determine if product should be considered in stock.
     *
     * @param Product $product
     * @param int $qty
     * @param int $threshold
     * @return bool
     */
    private function determineInStock(Product $product, int $qty, int $threshold): bool
    {
        $inStock = $qty > 0;
        $backorders = $product->getStockData()['backorders'] ?? null;
        if ($backorders === 4 && $qty <= $threshold) {
            return false;
        }
        return $inStock;
    }

    /**
     * Check if product availability attribute should be updated.
     *
     * @param Product $product
     * @param bool $inStock
     * @return bool
     */
    private function canUpdateAvailability(Product $product, bool $inStock): bool
    {
        $stockStatus = $product->getQuantityAndStockStatus();
        return is_array($stockStatus)
            && !empty($stockStatus['is_in_stock'])
            && $inStock;
    }

    /**
     * Retrieve the availability option ID for the "product_availability" attribute.
     *
     * @param Product $product
     * @return int|string|null
     */
    private function getAvailabilityOptionId(Product $product): int|string|null
    {
        $attribute = $product->getResource()->getAttribute('product_availability');
        if (!$attribute) {
            return null;
        }
        $options = $attribute->getOptions();
        if (empty($options)) {
            return null;
        }
        foreach ($options as $option) {
            if (!empty($option->getValue())) {
                return $attribute->getSource()->getOptionId($option->getValue());
            }
        }
        return null;
    }
}
