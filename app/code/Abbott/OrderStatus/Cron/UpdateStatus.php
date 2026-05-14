<?php
namespace Abbott\OrderStatus\Cron;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Abbott\OrderStatus\Helper\Data as dataHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Cron For Update Status
 */
class UpdateStatus
{
    public const STATUS = 'canceled';

    /**
     * @var DataHelper
     */
    protected dataHelper $dataHelper;

    /**
     * @var CollectionFactory
     */

    protected CollectionFactory $orderCollectionFactory;

    /**
     * @var OrderInterface
     */
    protected OrderInterface $orderInterface;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var DateTime
     */

    protected DateTime $date;

    /**
     * Config constructor.
     *
     * @param CollectionFactory $orderCollectionFactory
     * @param DateTime $date
     * @param OrderInterface $orderInterface
     * @param OrderRepositoryInterface $orderRepository ,
     * @param DataHelper $dataHelper
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        DateTime $date,
        OrderInterface $orderInterface,
        OrderRepositoryInterface $orderRepository,
        DataHelper $dataHelper
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->date = $date;
        $this->orderInterface = $orderInterface;
        $this->orderRepository = $orderRepository;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Handles order status when status is pending
     */
    public function execute(): void
    {
        if ($this->dataHelper->getModuleEnable() && $this->dataHelper->getCronEnable()) {
            $days = $this->dataHelper->getNumberDays();
            $endDate = $this->date->date('Y-m-d h:i:s');
            $startDate = $this->date->date('Y-m-d h:i:s', strtotime($endDate." -".$days." days"));
            $stores = $this->dataHelper->getStoresApplied();
            $paymentMethods = explode(",", $this->dataHelper->getPaymentMethod());
            $collection = $this->orderCollectionFactory->create()->addAttributeToSelect('*')
                ->addFieldToFilter('store_id', ['in' => $stores])
                ->addFieldToFilter('status', 'pending')
                ->addFieldToFilter(
                    'created_at',
                    ['lt'=>$startDate]
                );
            $joinQuery = $collection->getSelect()->join(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )->where('sop.method IN(?)', $paymentMethods);
            $ordersData = $collection->load($joinQuery)->getData();
            if (!empty($ordersData)) {
                foreach ($ordersData as $val) {
                    $order = $this->orderInterface->loadByIncrementId($val['increment_id']);
                    $order->setState(self::STATUS);
                    $order->setStatus(self::STATUS);
                    $order->addStatusHistoryComment(
                        'Order status changed from pending to cancelled because no payment was received within '
                        .$days.' days of order placement'
                    )->setIsVisibleOnFront(true);
                    $this->orderRepository->save($order);
                }
            }

        }
    }
}
