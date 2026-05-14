<?php

namespace Abbott\PowerbiExport\Api\Data;

/**
 * Interface for Powerbi search results.
 * @api
 */
interface PowerbiSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Powerbi list
     * @return \Abbott\PowerbiExport\Api\Data\PowerbiInterface[]
     */
    public function getItems();
    /**
     * Set Powerbi list
     * @param \Abbott\PowerbiExport\Api\Data\PowerbiInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
