<?php


namespace Abbott\SmartCart\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SmartCartInterface
 */
interface SmartCartInterface extends ExtensibleDataInterface
{

    public const ATTR_ID = "entity_id";
    public const ATTR_CODE = "code";
    public const ATTR_PRODUCTS = "products";
    public const ATTR_DISCOUNT_RULE_ID = "discount_rule_id";
    public const ATTR_IS_ACTIVE = "is_active";
    public const ATTR_CREATED_AT = "created_at";
    public const ATTR_UPDATED_AT = "updated_at";
    public const ATTR_STORE_ID = "store_id";

    /**
     * GetId
     *
     * @return int
     */
    public function getId();

    /**
     * SetId
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * GetCode
     *
     * @return string
     */
    public function getCode();

    /**
     * SetCode
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * GetProducts
     *
     * @return string[]
     */
    public function getProducts();

    /**
     * SetProducts
     *
     * @param string[] $products
     * @return $this
     */
    public function setProducts($products);

    /**
     * GetDiscountRuleID
     *
     * @return int
     */
    public function getDiscountRuleId();

    /**
     * SetDiscountRuleId
     *
     * @param int $id
     * @return $this
     */
    public function setDiscountRuleId($id);

    /**
     * IsActive
     *
     * @return boolean
     */
    public function isActive();

    /**
     * SetIsActive
     *
     * @param boolean $active
     * @return $this
     */
    public function setIsActive($active);

    /**
     * GetIsActive
     *
     * @return boolean
     */
    public function getIsActive();

    /**
     * GetCreatedAt
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * SetCreatedAt
     *
     * @param string $date
     * @return $this
     */
    public function setCreatedAt($date);

    /**
     * GetUpdatedAt
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * SetUpdatedAt
     *
     * @param string $date
     * @return $this
     */
    public function setUpdatedAt($date);

    /**
     * GetStoreId
     *
     * @return $this
     */
    public function getStoreId();

    /**
     * SetStoreId
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id);
}
