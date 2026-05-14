<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Order\Item\Plugin;

use Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor as OrderItemOptionProcessor;
use Magento\Framework\App\State as AppState;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductOptions
 * @package Aheadworks\Sarp2\Model\Sales\Order\Item\Plugin
 */
class ProductOptions
{
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
        StoreManagerInterface $storeManager
    ) {
        $this->optionProcessor = $optionProcessor;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
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
        if ($this->optionProcessor->isSubscription($options)) {
            $storeId = $this->storeManager->getStore()->getId();
            $subscriptionOptions = $this->optionProcessor->getDetailedSubscriptionOptions(
                $options,
                $storeId,
                $this->isAdmin()
            );
            if (isset($options['options'])) {
                $this->optionProcessor->removeSubscriptionOptions($options['options']);
                $options['options'] = array_merge($options['options'], $subscriptionOptions);
            } else {
                $options['options'] = $subscriptionOptions;
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
