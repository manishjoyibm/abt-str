<?php
namespace Abbott\CustomerTwoFactorAuth\Api\Data;

/**
 * Interface for customer search results.
 * @api
 */
interface OtpSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customers list.
     *
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface[]
     */
    public function getItems();

    /**
     * Set customers list.
     *
     * @param \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
