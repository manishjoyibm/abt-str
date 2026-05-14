<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler\Info;

use Aheadworks\Sarp2\Model\Payment\Sampler\Info as SamplerInfo;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler\Info as SamplerInfoResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler\Info
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = SamplerInfoResource::ID_FIELD_NAME;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(SamplerInfo::class, SamplerInfoResource::class);
    }
}
