<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Rule;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    public function clearResult(): void
    {
        $this->_productIds = null;
        $this->_conditions = null;
    }
}
