<?php

namespace Abbott\Hartehanks\Api\Data;

interface HarteHankSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get HarteHank list.
     * @return \Abbott\Hartehanks\Api\Data\HarteHankInterface[]
     */
    public function getItems();

    /**
     * Set product_id list.
     * @param \Abbott\Hartehanks\Api\Data\HarteHankInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
