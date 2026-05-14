<?php

namespace Abbott\Hartehanks\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
use Magento\Framework\Exception\AlreadyExistsException;

class OrderStatus implements DataPatchInterface
{
    const ORDER_STATUS_SENT_TO_WAREHOUSE_CODE = 'sent_to_warehouse';

    const ORDER_STATUS_SENT_TO_WAREHOUSE_LABEL = 'Sent to Warehouse';

    const ORDER_STATUS_FAILED_CODE = 'failed';

    const ORDER_STATUS_FAILED_LABEL = 'Failed';

    const ORDER_STATUS_RETURNED_CODE = 'return';

    const ORDER_STATUS_RETURNED_LABEL = 'Return';

    const ORDER_STATUS_PARTIAL_SHIPPED_CODE = 'partially_shipped';

    const ORDER_STATUS_PARTIAL_SHIPPED_LABEL = 'Partially Shipped';

    const ORDER_STATUS_PARTIAL_INVOICED_CODE = 'partially_invoiced';

    const ORDER_STATUS_PARTIAL_INVOICED_LABEL = 'Partially Invoiced';

    const ORDER_STATUS_BACKORDERED_CODE = 'backordered';

    const ORDER_STATUS_BACKORDERED_LABEL = 'BackOrdered';

    /* @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /* @var StatusFactory */
    private $statusFactory;

    /* @var StatusResourceFactory */
    private $statusResourceFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    public function apply()
    {
        $this->addNewOrderStatus();
    }

    protected function addNewOrderStatus()
    {
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        $statusArray = [
          [
              'status' => self::ORDER_STATUS_SENT_TO_WAREHOUSE_CODE,
              'label' => self::ORDER_STATUS_SENT_TO_WAREHOUSE_LABEL,
          ],
          [
              'status' => self::ORDER_STATUS_FAILED_CODE,
              'label' => self::ORDER_STATUS_FAILED_LABEL,
          ],
          [
              'status' => self::ORDER_STATUS_RETURNED_CODE,
              'label' => self::ORDER_STATUS_RETURNED_LABEL,
          ],
          [
              'status' => self::ORDER_STATUS_PARTIAL_SHIPPED_CODE,
              'label' => self::ORDER_STATUS_PARTIAL_SHIPPED_LABEL,
          ],
          [
              'status' => self::ORDER_STATUS_PARTIAL_INVOICED_CODE,
              'label' => self::ORDER_STATUS_PARTIAL_INVOICED_LABEL,
          ],
          [
              'status' => self::ORDER_STATUS_BACKORDERED_CODE,
              'label' => self::ORDER_STATUS_BACKORDERED_LABEL,
          ]
        ];
        foreach ($statusArray as $newStatus) {
            $status->setData($newStatus);
            try {
                $statusResource->save($status);
            } catch (AlreadyExistsException $exception) {
                return;
            }
            if ($newStatus['status'] == self::ORDER_STATUS_RETURNED_CODE) {
                $status->assignState(Order::STATE_COMPLETE, false, true);
            } else {
                $status->assignState(Order::STATE_PROCESSING, false, true);
            }
        }
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
