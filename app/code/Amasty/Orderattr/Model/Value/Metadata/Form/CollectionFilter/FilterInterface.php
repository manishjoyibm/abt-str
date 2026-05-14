<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Value\Metadata\Form\CollectionFilter;

use Amasty\Orderattr\Model\ResourceModel\Attribute\Collection;

interface FilterInterface
{
    /**
     * @param Collection $collection
     * @param array|null $customAttributes
     * @return void
     * @throws \Exception
     */
    public function apply(Collection $collection, array $customAttributes = null): void;
}
