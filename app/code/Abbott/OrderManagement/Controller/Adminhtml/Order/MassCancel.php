<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Abbott\OrderManagement\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Abbott\OrderManagement\Helper\Data as OrderManagementHelper;
use Magento\Framework\Controller\Result\RedirectFactory;

class MassCancel extends \Magento\Sales\Controller\Adminhtml\Order\MassCancel
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::cancel';

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var \Abbott\OrderManagement\Helper\Data
     */
    protected $helper;

    protected $errorMessage = "You cannot cancel the order.";

    protected $resultRedirectFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface|null $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementHelper $helper,
        RedirectFactory $resultRedirectFactory,
        OrderManagementInterface $orderManagement = null,
    ) {
        parent::__construct($context, $filter, $collectionFactory, $orderManagement);
        $this->collectionFactory = $collectionFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderManagement = $orderManagement ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Sales\Api\OrderManagementInterface::class
        );
        $this->helper = $helper;
    }

    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $countCancelOrder = 0;


        foreach ($collection->getItems() as $order) {
            $checkProgressiveAndBuyersRemorse = $this->helper->checkIsProgressiveAndBuyersRemorse(
                $order->getEntityId()
            );

            $isProgressive = isset(
                $checkProgressiveAndBuyersRemorse['is_progressive']
            ) ? $checkProgressiveAndBuyersRemorse['is_progressive'] : "";

            $isCancel = isset(
                $checkProgressiveAndBuyersRemorse['is_cancel_order']
            ) ? $checkProgressiveAndBuyersRemorse['is_cancel_order'] : "";

            $timeDiffInSeconds = (
                strtotime(
                    $order->getCreatedAt()
                ) + $this->helper->getTime()
                )-(
                    strtotime(
                        date('Y-m-d H:i:s')
                    )
                );
            $errorMessageCheck = 0;
            if ($isProgressive == 1 && $isCancel == 0):
                $errorMessageCheck = 1;
            endif;

            if ($isProgressive == 1 && $isCancel == 1) {
                if ($timeDiffInSeconds > 0):
                    if ($order->getStatus() == 'processing' &&
                        $order->getShippingMethod() == OrderManagementHelper::FEDEX_STD_OVERNIGHT_SHIPPING
                    ) {
                           $errorMessageCheck = 1;
                    } else {
                          $this->orderManagement->cancel($order->getEntityId());
                    }
                else:
                            $errorMessageCheck = 1;
                endif;
            }

            if ($errorMessageCheck == 1):
                $this->messageManager->addErrorMessage(__($this->errorMessage));
                $resultRedirect->setPath($this->getComponentRefererUrl());
                return $resultRedirect;
            endif;

            if (!($order->getStatus() == 'processing' && ($timeDiffInSeconds > 0) &&
                ($order->getShippingMethod() != OrderManagementHelper::FEDEX_STD_OVERNIGHT_SHIPPING))
            ) {
                    $this->messageManager->addErrorMessage(__($this->errorMessage));
                    $resultRedirect->setPath($this->getComponentRefererUrl());
                    return $resultRedirect;
            } else {
                    $isCanceled = $this->orderManagement->cancel($order->getEntityId());
            }

            if (!isset($isCanceled)):
                    $isCanceled = $this->orderManagement->cancel($order->getEntityId());
            endif;

            if ($isCanceled === false) {
                continue;
            }
                $countCancelOrder++;
        }
        $countNonCancelOrder = $collection->count() - $countCancelOrder;

        if ($countNonCancelOrder && $countCancelOrder) {
            $this->messageManager->addErrorMessage(__('%1 order(s) cannot be canceledaa.', $countNonCancelOrder));
        } elseif ($countNonCancelOrder) {
            $this->messageManager->addErrorMessage(__('You cannot cancel the order(s).'));
        }

        if ($countCancelOrder) {
            $this->messageManager->addSuccessMessage(__('We canceled %1 order(s).', $countCancelOrder));
        }

        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
