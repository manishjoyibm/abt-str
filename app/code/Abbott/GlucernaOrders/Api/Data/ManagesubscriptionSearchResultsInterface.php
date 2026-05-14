<?php

namespace Abbott\GlucernaOrders\Api\Data;

interface ManagesubscriptionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get managesubscription list.
     * @return \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set plan_code list.
     * @param \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
