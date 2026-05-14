<?php

declare(strict_types=1);

namespace Abbott\Hartehanks\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class HhProcessingOrder extends AbstractDb
{
    public const TBL_NAME = 'apollo_hartehank_processing_orders';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TBL_NAME, 'entity_id');
    }

    /**
     * Check if Order is Locked
     *
     * @param int $id
     * @return bool
     * @throws LocalizedException
     */
    public function isOrderLocked(int $id): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), 'count(*)');
        $select->where('order_id = ?', $id);
        return (bool)$connection->fetchOne($select);
    }
}
