<?php

namespace Abbott\OrderManagement\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Rma\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * RMA entity resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Item extends \Magento\Rma\Model\ResourceModel\Item
{

    /**
     * Rma data
     *
     * @var \Magento\Rma\Helper\Data
     */
    protected $rmaData;

    /**
     * @var \Magento\Sales\Model\Order\Admin\Item
     */
    protected $adminOrderItem;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Rma\Helper\Data $rmaData
     * @param \Magento\Rma\Model\ResourceModel\Order\Item\CollectionFactory $ordersFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $refundableList
     * @param \Magento\Sales\Model\Order\Admin\Item $adminOrderItem
     * @param array $data
     * @param ProductCollectionFactory|null $productCollectionFactory
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Rma\Helper\Data $rmaData,
        \Magento\Rma\Model\ResourceModel\Order\Item\CollectionFactory $ordersFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $refundableList,
        \Magento\Sales\Model\Order\Admin\Item $adminOrderItem,
        $data = [],
        ProductCollectionFactory $productCollectionFactory = null
    ) {
        $this->adminOrderItem = $adminOrderItem;
        $this->rmaData = $rmaData;
        $this->productCollectionFactory = $productCollectionFactory
            ?? ObjectManager::getInstance()->get(ProductCollectionFactory::class);
        parent::__construct(
            $context,
            $rmaData,
            $ordersFactory,
            $productFactory,
            $refundableList,
            $adminOrderItem,
            $data
        );
    }

    /**
     * Gets available order items collection
     *
     * @param  int $orderId
     * @param  int|bool $parentId if need retrieves only bundle and its children
     * @return \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    public function getOrderItems($orderId, $parentId = false)
    {
        /** @var $orderItemsCollection \Magento\Sales\Model\ResourceModel\Order\Item\Collection */
        $orderItemsCollection = $this->getOrderItemsCollection($orderId);

        if (!$orderItemsCollection->count()) {
            return $orderItemsCollection;
        }
        $returnableItems = $this->getReturnableItems($orderId);
        $orderProducts = $this->getOrderProducts($orderItemsCollection);
        /* @var $item \Magento\Sales\Model\Order\Item */
        foreach ($orderItemsCollection as $item) {
            $itemId = $item->getId();
            /* retrieves only bundle and children by $parentId */
            if ($parentId && $itemId != $parentId && $item->getParentItemId() != $parentId) {
                $orderItemsCollection->removeItemByKey($itemId);
                continue;
            }
            $canReturn = isset($returnableItems[$itemId]);
            /** @var \Magento\Catalog\Model\Product|null $product */
            $product = $orderProducts[$this->adminOrderItem->getProductId($item)] ?? null;
            $canReturnProduct = $this->canReturnProduct($product, $item);

            if (!$canReturn || !$canReturnProduct) {
                $orderItemsCollection->removeItemByKey($itemId);
                continue;
            }
            if ($item->getAvailableQty() > $returnableItems[$itemId]) {
                $item->setAvailableQty($returnableItems[$itemId]);
            }
        }

        return $orderItemsCollection;
    }

    /**
     * Return products from order items
     *
     * @param Collection $orderItems
     * @return array
     */
    private function getOrderProducts(Collection $orderItems): array
    {
        $productsIds = [];
        foreach ($orderItems as $item) {
            $productsIds[] = $this->adminOrderItem->getProductId($item);
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setFlag('has_stock_status_filter', false);
        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns($collection->getIdFieldName());

        $collection->addAttributeToSelect('is_returnable');
        $collection->addFieldToFilter($collection->getIdFieldName(), ['in' => $productsIds]);

        return $collection->getItems();
    }

    /**
     * Verifying that product can be returned.
     *
     * @param \Magento\Catalog\Model\Product|null $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    private function canReturnProduct($product, \Magento\Sales\Model\Order\Item $item): bool
    {
        return $product !== null
            ? $this->rmaData->canReturnProduct($product, $item->getStoreId())
            : false;
    }
}
