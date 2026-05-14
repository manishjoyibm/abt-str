<?php

namespace Abbott\Checkout\Observer;

use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Abbott\Checkout\Model\ProductAvailabilityAttribute as ProductAvailability;

class UpdateProductAvailabilityAttr implements ObserverInterface
{
    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var ProductAvailability
     */
    protected ProductAvailability $productAvailability;

    /**
     * @param ProductRepository $productRepository
     * @param LoggerInterface $logger
     * @param ProductAvailability $productAvailability
     */
    public function __construct(
        ProductRepository $productRepository,
        LoggerInterface $logger,
        ProductAvailability $productAvailability
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->productAvailability = $productAvailability;
    }

    /**
     * Execute function
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderItems = $observer->getOrder()->getAllItems();
        $storeId = $observer->getOrder()->getStoreId();

        try {
            foreach ($orderItems as $item) {
                $productId = $item->getProductId();
                $product = $this->productRepository->getById($productId);
                if ($product->getTypeInstance() instanceof Simple) {
                    $this->productAvailability->updateProductAvailability($product, $storeId);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
