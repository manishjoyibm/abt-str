<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Email\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Block\Email\Items\AbstractItems;

/**
 * Class Items
 *
 * @method ProfileInterface getProfile()
 *
 * @package Aheadworks\Sarp2\Block\Email\Profile
 */
class Items extends AbstractItems
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'email/profile/items.phtml';

    /**
     * {@inheritdoc}
     */
    protected function getItemType($item)
    {
        /** @var ProfileItemInterface $item */
        return $item->getProductType();
    }
}
