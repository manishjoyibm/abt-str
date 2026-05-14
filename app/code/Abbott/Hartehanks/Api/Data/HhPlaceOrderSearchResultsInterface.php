<?php

namespace Abbott\Hartehanks\Api\Data;

interface HhPlaceOrderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get HhPlaceOrder list.
     * @return \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface[]
     */
    public function getItems();

    /**
     * Set order_id list.
     * @param \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
