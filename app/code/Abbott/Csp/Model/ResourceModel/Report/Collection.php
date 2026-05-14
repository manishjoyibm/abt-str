<?php
declare(strict_types=1);

namespace Abbott\Csp\Model\ResourceModel\Report;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(\Abbott\Csp\Model\Report::class, \Abbott\Csp\Model\ResourceModel\Report::class);
    }
}
