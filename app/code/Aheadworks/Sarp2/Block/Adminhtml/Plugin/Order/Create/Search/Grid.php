<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Adminhtml\Plugin\Order\Create\Search;

use Aheadworks\Sarp2\Model\Product\Attribute\Source\SubscriptionType;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid as SearchGrid;

/**
 * Class Grid
 * @package Aheadworks\Sarp2\Block\Adminhtml\Plugin\Order\Create\Search
 */
class Grid
{
    /**
     * @param SearchGrid $grid
     * @param Collection $collection
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetCollection(
        SearchGrid $grid,
        $collection
    ) {
        $collection->addAttributeToFilter(
            'aw_sarp2_subscription_type',
            [
                'or' => [
                    ['neq' => SubscriptionType::SUBSCRIPTION_ONLY],
                    ['is' => new \Zend_Db_Expr('null')]
                ]
            ],
            'left'
        );
        return [$collection];
    }
}
