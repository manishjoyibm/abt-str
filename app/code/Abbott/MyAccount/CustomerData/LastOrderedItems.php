<?php

namespace Abbott\MyAccount\CustomerData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface;
use Abbott\ProgressiveDiscount\Helper\Data;

/**
 * Returns information for "Recently Ordered" widget.
 * It contains list of 5 salable products from the last placed order.
 * Qty of products to display is limited by LastOrderedItems::SIDEBAR_ORDER_LIMIT constant.
 */
class LastOrderedItems extends \Magento\Sales\CustomerData\LastOrderedItems
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->helper = $helper;
        parent::__construct(
            $orderCollFactory,
            $orderConfig,
            $customerSession,
            $stockRegistry,
            $storeManager,
            $productRepository,
            $logger
        );
    }

    /**
     * Init last placed customer order for display on front
     *
     * @param int $customerId
     * @return void
     * @throws NoSuchEntityException
     */
    public function getOrders($customerId = null)
    {
        if (!$customerId) {
            $customerId = $this->_customerSession->getCustomerId();
        }
        $orders = $this->_orderCollectionFactory->create()
            ->addAttributeToFilter('customer_id', $customerId)
            ->addAttributeToFilter('store_id', $this->storeManager->getStore()->getId())
            ->addAttributeToFilter('status', ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()])
            ->addAttributeToSort('created_at', 'desc')
            ->setPage(1, 1);
        $this->orders = $orders;
    }

    /**
     * Get list of last ordered products
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderItems()
    {
        $items = [];
        $order = $this->getLastOrder();
        $limit = 100;
        if ($order) {
            $website = $this->storeManager->getStore()->getWebsiteId();
            /** @var Item $item */
            foreach ($order->getParentItemsRandomCollection($limit) as $item) {
                /** @var Product $product */
                try {
                    $subOpt = null;
                    $options = $item->getProductOptions();
                    if ($options &&
                     !empty($options['info_buyRequest']['aw_sarp2_subscription_type']) &&
                     !empty($options['aw_sarp2_subscription_plan']['plan_id'])
                     ) {
                        $planId = $options['aw_sarp2_subscription_plan']['plan_id'];
                        $subOpt = $options['info_buyRequest']['aw_sarp2_subscription_type'];
                        if ($this->helper->getIsProgressiveCheckoutRestricted() &&
                         $this->helper->getIsProgressive($planId)) {
                            continue;
                        }
                    }
                    $product = $this->productRepository->getById(
                        $item->getProductId(),
                        false,
                        $this->storeManager->getStore()->getId()
                    );
                } catch (NoSuchEntityException $noEntityException) {
                    $this->logger->critical($noEntityException);
                    continue;
                }
                $items = $this->getItemsData($product, $website, $item, $subOpt, $items);
            }
        }
        return $items;
    }

    /**
     * GetItems function
     *
     * @param ProductInterface|Product $product
     * @param int $website
     * @param Item $item
     * @param mixed $subOpt
     * @param array $items
     * @return array
     */
    public function getItemsData(
        ProductInterface|Product $product,
        int $website,
        Item $item,
        mixed $subOpt,
        array $items
    ): array {
        if (isset($product) && in_array($website, $product->getWebsiteIds())) {
            $items[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'url' => $product->getAemUrl(),
                'sku' => $product->getSku(),
                'is_saleable' => $this->isItemAvailableForReorder($item),
                'meta_title' => $product->getMetaTitle(),
                'aw_sarp2_subscription_type' => $subOpt,
                'quantity' => intval($item->getQtyOrdered())
            ];
        }
        return $items;
    }
}
