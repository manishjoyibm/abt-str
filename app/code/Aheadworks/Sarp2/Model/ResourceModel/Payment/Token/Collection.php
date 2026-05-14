<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel\Payment\Token;

use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Payment\Token
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'token_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Token::class, TokenResource::class);
    }
}
