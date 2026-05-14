<?php

namespace Abbott\Hartehanks\Setup\Patch\Data;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class OrderStatusV2 implements DataPatchInterface
{
    const ORDER_STATUS_PENDING_INVOICE_CODE = 'pending_invoice';

    const ORDER_STATUS_PENDING_INVOICE_LABEL = 'Pending Invoice';

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
                'status' => self::ORDER_STATUS_PENDING_INVOICE_CODE,
                'label' => self::ORDER_STATUS_PENDING_INVOICE_LABEL,
            ]
        ];
        foreach ($statusArray as $newStatus) {
            $status->setData($newStatus);
            try {
                $statusResource->save($status);
            } catch (AlreadyExistsException $exception) {
                return;
            }
            $status->assignState(Order::STATE_PROCESSING, false, true);
        }
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [OrderStatus::class];
    }
}
