<?php

namespace Abbott\WorkdayFeed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for InboundFeed Log search results.
 * @api
 */
interface InboundFeedLogSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get WorkDay Feed list
     *
     * @return InboundFeedLogInterface[]
     */
    public function getItems(): array;
    /**
     * Set WorkDay Feed list
     *
     * @param InboundFeedLogInterface[] $items
     * @return $this
     */
    public function setItems(array $items): static;
}
