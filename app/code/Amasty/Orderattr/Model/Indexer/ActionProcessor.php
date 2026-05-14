<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Indexer;

use Magento\Framework\Indexer\AbstractProcessor;
use Amasty\Orderattr\Model\ResourceModel\Entity\Entity;

class ActionProcessor extends AbstractProcessor
{
    public const INDEXER_ID = Entity::GRID_INDEXER_ID;
}
