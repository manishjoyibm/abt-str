<?php

namespace Abbott\ProgressiveDiscount\Api\Data;

interface ManageDiscountCodesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const ROW_ID    = 'row_id';
    public const DISCOUNT  = 'discount';
    public const PLAN      = 'plan';
    public const MONTHS    = 'months';
    public const PROMOTIONAL_SKU = 'promotional_sku';

    /**
     * Get row_id
     *
     * @return string|null
     */
    public function getRowId();

    /**
     * Set row_id
     *
     * @param string $rowId
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setRowId($rowId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesExtensionInterface $extensionAttributes
    );

    /**
     * Get months
     *
     * @return string|null
     */
    public function getMonths();

    /**
     * Set months
     *
     * @param string $months
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setMonths($months);

    /**
     * Get discount
     *
     * @return string|null
     */
    public function getDiscount();

    /**
     * Set discount
     *
     * @param string $discount
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setDiscount($discount);

    /**
     * Get plan
     *
     * @return string|null
     */
    public function getPlan();

    /**
     * Set plan
     *
     * @param string $plan
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setPlan($plan);
    /**
     * Get promotionalSku
     *
     * @return string|null
     */
    public function getPromotionalSku();

    /**
     * Set promotionalSku
     *
     * @param string $promotionalSku
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setPromotionalSku($promotionalSku);
}
