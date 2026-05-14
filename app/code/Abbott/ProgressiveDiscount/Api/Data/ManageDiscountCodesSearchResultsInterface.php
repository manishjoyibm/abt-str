<?php

namespace Abbott\ProgressiveDiscount\Api\Data;

interface ManageDiscountCodesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get ManageDiscountCodes list.
     *
     * @return ManageDiscountCodesInterface[]
     */
    public function getItems();

    /**
     * Set row_id list.
     *
     * @param ManageDiscountCodesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
