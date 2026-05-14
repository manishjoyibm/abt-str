<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Email\Order;

use Aheadworks\Sarp2\Block\Email\Items\AbstractItems;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Class Items
 *
 * @method OrderItemInterface getOrder()
 *
 * @package Aheadworks\Sarp2\Block\Email\Order
 */
class Items extends AbstractItems
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'email/order/items.phtml';

    /**
     * {@inheritdoc}
     */
    protected function getItemType($item)
    {
        /** @var OrderItemInterface $item */
        return $item->getProductType();
    }
}
