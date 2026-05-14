<?php

namespace Abbott\Checkout\Observer;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use \Psr\Log\LoggerInterface;
use Abbott\MyAccount\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class UpdateSoldToQty implements ObserverInterface
{
    protected $productFactory;

    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        ProductFactory $productFactory,
        LoggerInterface $logger,
        StoreManagerInterface $store
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->storeManager = $store;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if ($this->storeManager->getStore()->getCode() == Data::NEW_SIM_STORE_CODE) {
            return;
        }

        $orderItems = $observer->getOrder()->getAllItems();
        try {
            foreach ($orderItems as $item) {
                $sku = $item->getSku();
                $product = $this->productFactory->create()->loadByAttribute('sku', $sku);
                $currentSoldQty = $product->getData('product_sold_qty');
                $orderdQty = $item->getQtyOrdered();
                $soldTOQty = $currentSoldQty + $orderdQty;
                $product->setCustomAttribute("product_sold_qty", $soldTOQty);
                $product->save($product);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
