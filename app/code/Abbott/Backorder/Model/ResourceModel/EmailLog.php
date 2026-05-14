<?php
namespace Abbott\Backorder\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class EmailLog
 *
 * Resource model for EmailLog entity.
 * Handles database operations for backorder email log records.
 *
 * @package Abbott\Backorder\Model\ResourceModel
 */
class EmailLog extends AbstractDb
{
    /**
     * Initialize resource model.
     *
     * Defines the main table and primary key for the EmailLog entity.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('abbott_backorder_email', 'id');
    }
}