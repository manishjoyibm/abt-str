<?php
namespace Abbott\OrderManagement\Plugin\Block\Widget\Button;

use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Abbott\OrderManagement\Helper\Data as OrderManagementHelper;
use Abbott\OrderManagement\ViewModel\CancelVisibility; 

class Toolbar
{
    /** @var OrderManagementHelper */
    public $helper;

    /** @var CancelVisibility */
    private $cancelVisibility; 

    public function __construct(
        OrderManagementHelper $helper,
        CancelVisibility $cancelVisibility 
    ) {
        $this->helper = $helper;
        $this->cancelVisibility = $cancelVisibility; 
    }

    public function beforePushButtons(
        ToolbarContext $toolbar,
        AbstractBlock $context,
        ButtonList $buttonList
    ) {
        if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\View) {
            return [$context, $buttonList];
        }

        $order = $context->getOrder();;

        //  read config-driven visibility (enabled + allowed statuses)
        $isAllowedStatus = $this->cancelVisibility->isCancelVisible($order);

        // Preserve existing buffer/time logic
        $timeDiffInSeconds = (
            strtotime($order->getCreatedAt()) + $this->helper->getTime()
        ) - (strtotime(date('Y-m-d H:i:s')));

        // Preserve existing progressive & buyer's remorse checks
        $checkProgressiveAndBuyersRemorse = $this->helper->checkIsProgressiveAndBuyersRemorse($order->getEntityId());
        $isProgressive = isset($checkProgressiveAndBuyersRemorse['is_progressive'])
            ? $checkProgressiveAndBuyersRemorse['is_progressive'] : "";
        $isCancel = isset($checkProgressiveAndBuyersRemorse['is_cancel_order'])
            ? $checkProgressiveAndBuyersRemorse['is_cancel_order'] : "";

        $cancelButtonCheck = 0;
        if ($isProgressive == 1 && $isCancel == 0):
            $cancelButtonCheck = 1;
        endif;

        if ($isProgressive == 1 && $isCancel == 1) {
            if ($timeDiffInSeconds > 0):
                if ($order->getStatus() == 'processing' &&
                    $order->getShippingMethod() == OrderManagementHelper::FEDEX_STD_OVERNIGHT_SHIPPING
                ) {
                    $cancelButtonCheck = 1;
                }
                return [$context, $buttonList];
            else:
                $cancelButtonCheck = 1;
            endif;
        }

        // If business logic allows cancel BUT current status is not allowed by config, remove button
        if ($cancelButtonCheck == 1 && !$isAllowedStatus):
            $buttonList->remove('order_cancel');
            return [$context, $buttonList];
        endif;

        // Keep your reorder removal
        $orderProfileCollection = $this->helper->getOrderProfiles($order->getEntityId());
        if ($orderProfileCollection->getSize()) {
            $buttonList->remove('order_reorder');
        }

        // Final condition to remove Cancel button when not allowed by config and not meeting special cases
        if (
            !($order->getStatus() == 'processing' &&
              ($timeDiffInSeconds > 0) &&
              ($order->getShippingMethod() != OrderManagementHelper::FEDEX_STD_OVERNIGHT_SHIPPING)
            )
            && !$isAllowedStatus
        ) {
            $buttonList->remove('order_cancel');
        }

        return [$context, $buttonList];
    }
}
