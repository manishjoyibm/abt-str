<?php

namespace Abbott\ProgressiveDiscount\Api\Data;

/**
 * Interface for ManageMonthlySubscriptions search results.
 * @api
 */
interface ManageMonthlySubscriptionsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * GetItems
     *
     * @return ManageMonthlySubscriptionsInterface[]
     */
    public function getItems();

    /**
     * SetItems
     *
     * @param ManageMonthlySubscriptionsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
