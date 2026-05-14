<?php

namespace Abbott\KountCustomFields\Model\Order\Action;

use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Kount\Kount360\Model\Config\Workflow;
use Kount\Kount360\Model\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Magento\Framework\Registry;
use Magento\Framework\Message\ManagerInterface;

class Decline extends \Kount\Kount360\Model\Order\Action\Decline
{
    /**
     * @var Workflow
     */
    protected $configWorkflow;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoManagementInterface
     */
    protected $creditmemoManagement;

    /**
     * @var Logger
     */
    protected $logger;
    private CollectionFactory $collectionFactory;
    private ProfileManagementInterface $profileManagement;
    private Registry $registry;
    private ManagerInterface $messageManager;

    /**
     * @param Workflow $configWorkflow
     * @param OrderRepositoryInterface $orderRepository
     * @param CreditmemoLoader $creditmemoLoader
     * @param CreditmemoManagementInterface $creditmemoManagement
     * @param Logger $logger
     * @param ProfileManagementInterface $profileManagement
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Workflow $configWorkflow,
        OrderRepositoryInterface $orderRepository,
        CreditmemoLoader $creditmemoLoader,
        CreditmemoManagementInterface $creditmemoManagement,
        Logger $logger,
        ProfileManagementInterface $profileManagement,
        CollectionFactory $collectionFactory,
        Registry $registry,
        ManagerInterface $messageManager
    ) {
        $this->configWorkflow = $configWorkflow;
        $this->orderRepository = $orderRepository;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoManagement = $creditmemoManagement;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->profileManagement = $profileManagement;
        parent::__construct(
            $configWorkflow,
            $registry,
            $orderRepository,
            $creditmemoLoader,
            $creditmemoManagement,
            $logger,
            $messageManager
        );
    }

    /**
     * Method cancel
     *
     * @param Order $order
     * @return bool
     * @throws LocalizedException
     */
    protected function cancel($order): bool
    {
        $profiles = $this->collectionFactory->create()->addFieldToFilter('last_order_id', $order->getId());
        if (!empty($profiles->getData())) {
            foreach ($profiles->getData() as $value) {
                $this->profileManagement->changeStatusAction($value['profile_id'], 'cancelled');
            }
        }
        $isCanceled = $this->orderCancel($order);
        $orderComment = $isCanceled
            ? __('Order cancelled / voided due to Kount RIS Decline.')
            : __('Failed to cancel order. Cancel attempt due to Kount RIS Decline.');
        $order->addStatusHistoryComment($orderComment);

        return $isCanceled;
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function orderCancel(Order $order): bool
    {
        $this->logger->info('Attempting to cancel Magento order.');

        if ($order->canCancel()) {
            $order->setData(OrderInterface::PAYMENT, null);
            $order->cancel();
            return true;
        }
        $this->logger->error('Unable to cancel Magento order.');
        return false;
    }
}
