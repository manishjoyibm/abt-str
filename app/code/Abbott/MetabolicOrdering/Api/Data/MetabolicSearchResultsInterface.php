<?php

namespace Abbott\MetabolicOrdering\Api\Data;

/**
 * Interface for Metabolic search results.
 * @api
 */
interface MetabolicSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Metabolic list
     *
     * @return \Abbott\MetabolicOrdering\Api\Data\MetabolicInterface[]
     */
    public function getItems();

    /**
     * Set Metabolic list
     *
     * @param \Abbott\MetabolicOrdering\Api\Data\MetabolicInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
