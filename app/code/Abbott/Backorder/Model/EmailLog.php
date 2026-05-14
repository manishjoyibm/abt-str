<?php
namespace Abbott\Backorder\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class EmailLog
 *
 * Represents a log entry for backorder email notifications.
 * This model is linked to the resource model that handles persistence.
 *
 * @package Abbott\Backorder\Model
 */
class EmailLog extends AbstractModel
{
    /**
     * Initialize resource model.
     *
     * This method binds the model to its corresponding resource model
     * for database operations.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(\Abbott\Backorder\Model\ResourceModel\EmailLog::class);
    }
}
