<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Ui\Component\Listing\Column;

/**
 * Class CustomerName
 * @package Aheadworks\Sarp2\Ui\Component\Listing\Column
 */
class CustomerName extends Link
{
    /**
     * {@inheritdoc}
     */
    protected function isLink(array $item)
    {
        return (bool)$item['customer_id'];
    }
}
