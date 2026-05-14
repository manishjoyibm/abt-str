<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\ResourceModel\Attribute\Relation;

use Amasty\Orderattr\Api\Data\RelationDetailInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RelationDetails extends AbstractDb
{
    public const TABLE_NAME = 'amasty_order_attribute_relation_details';

    public function _construct()
    {
        $this->_init(
            self::TABLE_NAME,
            RelationDetailInterface::RELATION_DETAIL_ID
        );
    }

    /**
     * Delete Details data for relation
     *
     * @param int $relationId
     */
    public function deleteAllDetailForRelation($relationId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['relation_id = ?' => $relationId]);
    }

    public function fastDelete($ids)
    {
        $db = $this->getConnection();
        $table = $this->getTable('amasty_order_attribute_relation_details');
        $db->delete($table, $db->quoteInto(RelationDetailInterface::RELATION_DETAIL_ID . ' IN(?)', $ids));
    }
}
