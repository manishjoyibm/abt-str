<?php

namespace Abbott\GigyaIM\Api\Data;

interface SsmCartSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get SsmShoppingCart list.
     *
     * @return \Abbott\GigyaIM\Api\Data\SsmCartInterface[]
     */
    public function getItems();

    /**
     * Set SsmShoppingCart list.
     *
     * @param \Abbott\GigyaIM\Api\Data\SsmCartInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
