<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\Relation;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Orderattr\Model\Attribute\Relation\Relation::class,
            \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\Relation::class
        );
    }
}
