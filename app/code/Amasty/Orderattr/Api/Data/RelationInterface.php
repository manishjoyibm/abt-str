<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Api\Data;

/**
 * Attributes Dependency
 */
interface RelationInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const RELATION_ID = 'relation_id';

    public const NAME = 'name';
    /**#@-*/

    /**
     * Returns Relation ID
     *
     * @return int
     */
    public function getRelationId();

    /**
     * @param int $relationId
     *
     * @return $this
     */
    public function setRelationId($relationId);

    /**
     * Returns Relation name
     *
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * @return \Amasty\Orderattr\Api\Data\RelationDetailInterface[]
     */
    public function getDetails();

    /**
     * @param \Amasty\Orderattr\Api\Data\RelationDetailInterface[] $relationDetails
     *
     * @return $this
     */
    public function setDetails($relationDetails);
}
