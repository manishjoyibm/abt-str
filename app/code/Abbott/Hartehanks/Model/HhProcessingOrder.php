<?php

declare(strict_types=1);

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Model\ResourceModel\HhProcessingOrder as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class HhProcessingOrder extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
