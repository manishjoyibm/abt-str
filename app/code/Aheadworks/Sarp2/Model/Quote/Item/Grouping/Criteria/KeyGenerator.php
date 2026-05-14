<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria;

use Aheadworks\Sarp2\Model\Quote\Item\Grouping\CriterionPool;
use Magento\Quote\Model\Quote\Item;

/**
 * Class KeyGenerator
 * @package Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria
 */
class KeyGenerator
{
    /**
     * @var CriterionPool
     */
    private $criterionPool;

    /**
     * @var KeyFactory
     */
    private $keyFactory;

    /**
     * @param CriterionPool $criterionPool
     * @param KeyFactory $keyFactory
     */
    public function __construct(
        CriterionPool $criterionPool,
        KeyFactory $keyFactory
    ) {
        $this->criterionPool = $criterionPool;
        $this->keyFactory = $keyFactory;
    }

    /**
     * Generate key
     *
     * @param Item $item
     * @param array $criteria
     * @return Key
     */
    public function generate($item, array $criteria)
    {
        $instances = [];
        $keyParts = [];
        foreach ($criteria as $criteriaCode) {
            $criterion = $this->criterionPool->getCriterion($criteriaCode);
            if ($criterion) {
                $value = $criterion->getValue($item);
                if ($value !== null) {
                    $keyParts[] = $value;
                    $instances[] = $criterion;
                }
            }
        }
        return $this->keyFactory->create(
            [
                'value' => implode('-', $keyParts),
                'criterionInstances' => $instances
            ]
        );
    }
}
