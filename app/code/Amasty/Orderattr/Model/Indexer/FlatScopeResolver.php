<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Indexer;

class FlatScopeResolver extends \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver
{
    public function resolve($index, array $dimensions)
    {
        $result = parent::resolve($index, $dimensions);

        foreach ($dimensions as $dimension) {
            $result .= $dimension->getValue();
        }

        return $result;
    }
}
