<?php

namespace Abbott\Sarp2\Model\Sales\Order\Item\Plugin;

use Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor as OrderItemOptionProcessor;
use Magento\Framework\App\State as AppState;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\StoreManagerInterface;

class ProductOptions
{
    public $dateTimeFactory;
    public $_snsOrder;
    const OPTIONS = 'options';
    
    /**
     * @var OrderItemOptionProcessor
     */
    private $optionProcessor;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param OrderItemOptionProcessor $optionProcessor
     * @param AppState $appState
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        OrderItemOptionProcessor $optionProcessor,
        AppState $appState,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Aheadworks\Sarp2\Model\Profile\Order $sns_order
    ) {
        $this->optionProcessor = $optionProcessor;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->_snsOrder = $sns_order;
    }

    /**
     * @param OrderItem $subject
     * @param array $options
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductOptions(OrderItem $subject, array $options)
    {
        $orderDate = $subject->getCreatedAt();
        $formattedDate = $this->dateTimeFactory->create()->date('Y-m-d', $orderDate);
        $sns = $this->_snsOrder->getCollection()->addFieldToFilter('order_id',['eq'=>$subject->getOrderId()]); 

          $sns ->join(
            ['profile'=>'aw_sarp2_profile'],
            "main_table.profile_id = profile.profile_id"
        );
        $start_date = $this->dateTimeFactory->create()->date('Y-m-d', $sns->getFirstItem()->getStartDate());
        if ($this->optionProcessor->isSubscription($options)) {
            $storeId = $this->storeManager->getStore()->getId();
            $orderId= $subject->getOrderId();
            $subscriptionOptions = $this->optionProcessor->getCompleteSubscriptionOptions(
                $start_date,
                $options,
                $storeId,
                $this->isAdmin()
            );
            if (isset($options[self::OPTIONS])) {
                $this->optionProcessor->removeSubscriptionOptions($options[self::OPTIONS]);
                $options[self::OPTIONS] = array_merge($options[self::OPTIONS], $subscriptionOptions);
            } else {
                $options[self::OPTIONS] = $subscriptionOptions;
            }
        }

        return $options;
    }

    /**
     * Check if admin app state
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isAdmin()
    {
        return $this->appState->getAreaCode() == 'adminhtml';
    }
}
