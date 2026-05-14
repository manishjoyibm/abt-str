<?php


namespace Abbott\Targetbase\Observer;

use Abbott\Targetbase\Model\BaseTargetbase;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class ProcessOrder implements ObserverInterface
{
    protected Json $jsonSerializer;
    private BaseTargetbase $baseTargetbase;
    protected LoggerInterface $logger;

    /**
     * ProcessOrder constructor.
     * @param Json $jsonSerializer
     * @param BaseTargetbase $baseTargetbase
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $jsonSerializer,
        BaseTargetbase $baseTargetbase,
        LoggerInterface $logger
    ) {
        $this->baseTargetbase = $baseTargetbase;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $observer->getEvent()->getShipment();
            if ($shipment->getOrigData('entity_id')) {
                return;
            }
            foreach ($shipment->getAllItems() as $shipmentItem) {
                $orderItem = $shipmentItem->getOrderItem();
                if (null === $orderItem) {
                    continue;
                }
                if ($orderItem->getHasChildren()) {
                    if (!$orderItem->isDummy(true)) {
                        foreach ($orderItem->getChildrenItems() as $item) {
                            if ($item->getIsVirtual() || $item->getLockedDoShip()) {
                                continue;
                            }
                            $productOptions = $item->getProductOptions();
                            if (isset($productOptions['bundle_selection_attributes'])) {
                                $bundleSelectionAttributes = $this->jsonSerializer->unserialize(
                                    $productOptions['bundle_selection_attributes']
                                );
                                if ($bundleSelectionAttributes) {
                                    $qty = $bundleSelectionAttributes['qty'] * $shipmentItem->getQty();
                                    $this->baseTargetbase->insertOrderItemData($item, $qty);
                                    continue;
                                }
                            } else {
                                // configurable product
                                $this->baseTargetbase->insertOrderItemData($orderItem, $shipmentItem->getQty());
                            }
                        }
                    }
                } else {
                    if ($orderItem->getIsVirtual() || $orderItem->getLockedDoShip()) {
                        continue;
                    }
                    $this->baseTargetbase->insertOrderItemData($orderItem, $shipmentItem->getQty());
                }

            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
