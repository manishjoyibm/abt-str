<?php

namespace Abbott\Chargeback\Api\Data;

interface ChargebackSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Chargeback list.
     * @return \Abbott\Chargeback\Api\Data\ChargebackInterface[]
     */
    public function getItems();

    /**
     * Set order_id list.
     * @param \Abbott\Chargeback\Api\Data\ChargebackInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
