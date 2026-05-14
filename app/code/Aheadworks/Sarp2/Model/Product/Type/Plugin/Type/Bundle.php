<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Type\Plugin\Type;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;

/**
 * Class Bundle
 * @package Aheadworks\Sarp2\Model\Product\Type\Plugin\Type
 */
class Bundle
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @param IsSubscription $isSubscriptionChecker
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
    }

    /**
     * @param BundleProductType $subject
     * @param SelectionCollection $selectionCollection
     * @return SelectionCollection
     */
    public function afterGetSelectionsCollection(BundleProductType $subject, $selectionCollection)
    {
        $items = $selectionCollection->getItems();
        $selectionCollection->removeAllItems();

        foreach ($items as $item) {
            if (!$this->isSubscriptionChecker->check($item, true)) {
                try {
                    $selectionCollection->addItem($item);
                } catch (\Exception $e) {
                }
            }
        }

        return $selectionCollection;
    }
}
