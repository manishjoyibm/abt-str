<?php

namespace Abbott\ProgressiveDiscount\Model\Data;

use Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class ManageDiscountCodes extends AbstractExtensibleObject implements ManageDiscountCodesInterface
{
    /**
     * Get row_id
     *
     * @return string|null
     */
    public function getRowId()
    {
        return $this->_get(self::ROW_ID);
    }

    /**
     * Set row_id
     *
     * @param string $rowId
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setRowId($rowId)
    {
        return $this->setData(self::ROW_ID, $rowId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get months
     *
     * @return string|null
     */
    public function getMonths()
    {
        return $this->_get(self::MONTHS);
    }

    /**
     * Set months
     *
     * @param string $months
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setMonths($months)
    {
        return $this->setData(self::MONTHS, $months);
    }

    /**
     * Get discount
     *
     * @return string|null
     */
    public function getDiscount()
    {
        return $this->_get(self::DISCOUNT);
    }

    /**
     * Set discount
     *
     * @param string $discount
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
    }

    /**
     * Get plan
     *
     * @return string|null
     */
    public function getPlan()
    {
        return $this->_get(self::PLAN);
    }

    /**
     * Set plan
     *
     * @param string $plan
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setPlan($plan)
    {
        return $this->setData(self::PLAN, $plan);
    }
    /**
     * Get promotionalSku
     *
     * @return string|null
     */
    public function getPromotionalSku()
    {
        return $this->_get(self::PROMOTIONAL_SKU);
    }

    /**
     * Set promotionalSku
     *
     * @param string $promotionalSku
     * @return \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface
     */
    public function setPromotionalSku($promotionalSku)
    {
        return $this->setData(self::PROMOTIONAL_SKU, $promotionalSku);
    }
}
