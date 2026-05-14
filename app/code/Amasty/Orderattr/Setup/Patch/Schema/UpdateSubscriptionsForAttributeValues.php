<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Setup\Patch\Schema;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mview\View\SubscriptionFactory;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class UpdateSubscriptionsForAttributeValues implements SchemaPatchInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    public function __construct(IndexerRegistry $indexerRegistry, SubscriptionFactory $subscriptionFactory)
    {
        $this->indexerRegistry = $indexerRegistry;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return UpdateSubscriptionsForAttributeValues
     */
    public function apply()
    {
        $indexer = $this->indexerRegistry->get('amasty_order_attribute_grid');
        if ($this->isApply($indexer)) {
            $oldSubscription = $this->subscriptionFactory->create([
                'view' => $indexer->getView(),
                'tableName' => 'sales_order',
                'columnName' => 'entity_id',
                'subscriptionModel' => SubscriptionFactory::INSTANCE_NAME
            ]);
            $oldSubscription->remove(); // drop old triggers
            $indexer->getView()->subscribe(); // create new triggers
        }

        return $this;
    }

    private function isApply(IndexerInterface $indexer): bool
    {
        if (!$indexer->isScheduled()) {
            return false;
        }

        foreach ($indexer->getView()->getSubscriptions() as $subscriptionConfig) {
            if ($subscriptionConfig['name'] === 'sales_order') {
                return true;
            }
        }

        return false;
    }
}
