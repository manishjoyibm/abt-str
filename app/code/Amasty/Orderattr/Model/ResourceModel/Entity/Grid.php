<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\ResourceModel\Entity;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;

class Grid extends \Amasty\Orderattr\Model\ResourceModel\Entity\EntityData\Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFieldToFilter(
            CheckoutEntityInterface::PARENT_ENTITY_TYPE,
            CheckoutEntityInterface::ENTITY_TYPE_ORDER
        );
        $this->getSelect()->group($this->getIdFieldName());
    }
}
