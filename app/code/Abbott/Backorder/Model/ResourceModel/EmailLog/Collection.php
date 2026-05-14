<?php
namespace Abbott\Backorder\Model\ResourceModel\EmailLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * Represents a collection of EmailLog entities for backorder notifications.
 * Provides methods to filter and retrieve multiple EmailLog records from the database.
 *
 * @package Abbott\Backorder\Model\ResourceModel\EmailLog
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize collection model and resource model.
     *
     * This method binds the collection to its corresponding model and resource model classes.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            \Abbott\Backorder\Model\EmailLog::class,
            \Abbott\Backorder\Model\ResourceModel\EmailLog::class
        );
    }
}