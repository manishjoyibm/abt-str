<?php

namespace Abbott\Checkout\Observer;

use Abbott\Checkout\Model\ProductAvailabilityAttribute as ProductAvailability;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class UpdateProductAvailabilityAttrOnImport implements ObserverInterface
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
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param ProductRepository $productRepository
     * @param LoggerInterface $logger
     * @param ProductAvailability $productAvailability
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductRepository $productRepository,
        LoggerInterface $logger,
        ProductAvailability $productAvailability,
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->productAvailability = $productAvailability;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute function
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $bunch = $observer->getEvent()->getBunch();
        if (!$bunch) {
            return;
        }

        try {
            foreach ($bunch as $product) {
                $storeId = $this->storeManager->getStore($product['store_view_code'])->getId();
                $product = $this->productRepository->get($product['sku']);
                if ($product->getTypeInstance() instanceof Simple) {
                    $this->productAvailability->updateProductAvailability($product, $storeId);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
