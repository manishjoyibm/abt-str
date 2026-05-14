<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Api\Data;

interface CheckoutEntityInterface
{
    /**#@+
     * Values for parent_entity_type
     */
    public const ENTITY_TYPE_ORDER = 1;
    public const ENTITY_TYPE_QUOTE = 2;
    /**#@-*/

    /**#@+
     * Constants defined for keys of data array
     */
    public const ENTITY_ID = 'entity_id';
    public const PARENT_ID = 'parent_id';
    public const PARENT_ENTITY_TYPE = 'parent_entity_type';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutEntityInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getParentId();

    /**
     * @param int $parentId
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutEntityInterface
     */
    public function setParentId($parentId);

    /**
     * @return int
     */
    public function getParentEntityType();

    /**
     * @param int $parentEntityType
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutEntityInterface
     */
    public function setParentEntityType($parentEntityType);
}
