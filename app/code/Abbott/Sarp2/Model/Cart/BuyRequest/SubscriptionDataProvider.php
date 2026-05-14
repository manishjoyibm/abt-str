<?php

declare(strict_types=1);

namespace Abbott\Sarp2\Model\Cart\BuyRequest;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;

/**
 * Provides Subscription buy request data for adding products to cart
 */
class SubscriptionDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $subscription = $this->arrayManager->get('data/aw_sarp2_subscription_type', $cartItemData);
        if (!isset($subscription)) {
            return [];
        }

        $subscription = (int) $subscription;

        return ['aw_sarp2_subscription_type' => $subscription];
    }
}
