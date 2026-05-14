<?php

namespace Abbott\OrderManagement\Controller\Order;

use Abbott\OrderManagement\Helper\Data;
use Abbott\RestrictCheckout\Model\Restriction;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\ScopeInterface;
use Abbott\Hartehanks\Model\ResourceModel\HhProcessingOrder;

/**
 * Controller for cancel order
 */
class Cancel extends \Magento\Framework\App\Action\Action implements OrderInterface
{
    public $helper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    public $sgpRestriction;
    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $order;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var HhProcessingOrder
     */
    protected $hhProcessingOrders;

    /**
     * Cancel constructor.
     *
     * @param OrderManagementInterface $orderManagementInterface
     * @param OrderLoaderInterface $orderLoader
     * @param Session $session
     * @param Registry $registry
     * @param Data $helper
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Restriction $sgpRestriction
     * @param HhProcessingOrder $hhProcessingOrders
     */
    public function __construct(
        \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface,
        OrderLoaderInterface $orderLoader,
        \Magento\Checkout\Model\Session $session,
        Registry $registry,
        \Abbott\OrderManagement\Helper\Data $helper,
        Context $context,
        \Abbott\RestrictCheckout\Model\Restriction $sgpRestriction,
        HhProcessingOrder $hhProcessingOrders
    ) {
        $this->order = $orderManagementInterface;
        $this->session = $session;
        $this->orderLoader = $orderLoader;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->sgpRestriction = $sgpRestriction;
        $this->hhProcessingOrders = $hhProcessingOrders;
        parent::__construct($context);
    }

    /**
     * to cancel an order
     *
     * @return void
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $orderEntity = $this->registry->registry('current_order');
        $resultRedirect = $this->resultRedirectFactory->create();
        $orderCreatedTime = $orderEntity->getCreatedAt();
        $currentTime = date("Y-m-d h:i:s");
        $timeDiffInSeconds = strtotime($currentTime) - strtotime($orderCreatedTime);
        $bufferTime = $this->helper->getTime();

        $checkProgressiveAndBuyersRemorse = $this->helper->checkIsProgressiveAndBuyersRemorse(
            $orderEntity->getEntityId()
        );
        $isProgressive = isset(
            $checkProgressiveAndBuyersRemorse['is_progressive']
        ) ? $checkProgressiveAndBuyersRemorse['is_progressive'] : "";

        $isCancel = isset(
            $checkProgressiveAndBuyersRemorse['is_cancel_order']
        ) ? $checkProgressiveAndBuyersRemorse['is_cancel_order'] : "";

        try {
            if ($timeDiffInSeconds < $bufferTime) {
                if (in_array($orderEntity->getStatus(), ['pending','processing','fraud'])) {
                    if ($this->checkOrderLockStatus((int)$orderEntity->getId())) {
                        $this->messageManager->addError(__('Order cannot get cancelled.'));
                        return $resultRedirect->setPath('*/*/history');
                    }

                    if ($isProgressive == 1) {
                        if ($isCancel == 1) {
                            $this->order->cancel($orderEntity->getId());
                            $post = $this->getRequest()->getPostValue();
                            if (!empty($post)) {
                                $this->sgpRestriction->setRestrictionDetails();
                            }
                            $this->messageManager->addSuccess(__('The order has been Canceled successfully.'));
                            if ($this->helper->getMailEnabled()) {
                                $this->helper->sendCancelNotification($orderEntity->getId());
                            }
                        } else {
                            $this->messageManager->addError(
                                __('Order cannot get cancelled.')
                            );
                        }

                    } else {
                        $this->order->cancel($orderEntity->getId());
                        $post = $this->getRequest()->getPostValue();
                        if (!empty($post)) {
                            $this->sgpRestriction->setRestrictionDetails();
                        }
                        $this->messageManager->addSuccess(__('The order has been Canceled successfully.'));
                        if ($this->helper->getMailEnabled()) {
                            $this->helper->sendCancelNotification($orderEntity->getId());
                        }
                    }
                } else {
                    $this->messageManager->addError(
                        __('We can\'t process your request right now. Please try after some time.')
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Order cannot get cancelled after 2 hours of creation')
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->session->getUseNotice(true)) {
                $this->messageManager->addNotice($e->getMessage());
            } else {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/history');
    }

    /**
     * Check Order Locked Status
     *
     * @param int $orderId
     * @return bool
     * @throws LocalizedException
     */
    private function checkOrderLockStatus(int $orderId): bool
    {
        return $this->hhProcessingOrders->isOrderLocked($orderId);
    }
}
