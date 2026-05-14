<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Indexer\Conditions;

class AttributeIndexer extends AbstractIndexer
{
    public const TYPE = 'attribute';

    protected function getType(): string
    {
        return self::TYPE;
    }
}
