<?php

declare(strict_types=1);

namespace Abbott\Hartehanks\Model\ResourceModel\HhProcessingOrder;

use Abbott\Hartehanks\Model\HhProcessingOrder;
use Abbott\Hartehanks\Model\ResourceModel\HhProcessingOrder as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            HhProcessingOrder::class,
            ResourceModel::class
        );
    }
}
