<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Observer;

use Amasty\Orderattr\Model\Indexer\Conditions\ProductProcessor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * event name="catalog_product_save_after"
 */
class ReindexAfterSaveProduct implements ObserverInterface
{
    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    public function __construct(ProductProcessor $productProcessor)
    {
        $this->productProcessor = $productProcessor;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if ($product) {
            $this->productProcessor->reindexRow((int)$product->getId());
        }
    }
}
