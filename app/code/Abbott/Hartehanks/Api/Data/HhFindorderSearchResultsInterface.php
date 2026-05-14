<?php

namespace Abbott\Hartehanks\Api\Data;

interface HhFindorderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get HhFindorder list.
     * @return \Abbott\Hartehanks\Api\Data\HhFindorderInterface[]
     */
    public function getItems();

    /**
     * Set created_at list.
     * @param \Abbott\Hartehanks\Api\Data\HhFindorderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
