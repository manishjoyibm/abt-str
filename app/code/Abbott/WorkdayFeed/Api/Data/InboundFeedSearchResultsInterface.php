<?php

namespace Abbott\WorkdayFeed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for InboundFeed search results.
 * @api
 */
interface InboundFeedSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get WorkDay Feed list
     *
     * @return InboundFeedInterface[]
     */
    public function getItems(): array;
    /**
     * Set WorkDay Feed list
     *
     * @param InboundFeedInterface[] $items
     * @return $this
     */
    public function setItems(array $items): static;
}
