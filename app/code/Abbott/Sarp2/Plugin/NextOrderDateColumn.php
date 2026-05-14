<?php

namespace Abbott\Sarp2\Plugin;

use Aheadworks\Sarp2\Model\ResourceModel\Profile\Grid\Collection as SubscriptionProfileCollection;

/**
 * Class NextOrderDateColumn
 * @package Abbott\Sarp2\Plugin
 */
class NextOrderDateColumn
{
    /**
     * @var SubscriptionProfileCollection
     */
    private $subscriptionProfileCollection;

    /**
     * NextOrderDateColumn constructor.
     * @param SubscriptionProfileCollection $subscriptionProfileCollection
     */
    public function __construct(SubscriptionProfileCollection $subscriptionProfileCollection
    )
    {
        $this->subscriptionProfileCollection = $subscriptionProfileCollection;
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param \Closure $proceed
     * @param $requestName
     * @return SubscriptionProfileCollection
     */
    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    )
    {
        $result = $proceed($requestName);
        if ($requestName == 'aw_sarp2_subscription_listing_data_source') {
            if ($result instanceof $this->subscriptionProfileCollection) {
                $select = $this->subscriptionProfileCollection->getSelect();
                $select->joinLeft(
                    ["ascs" => "aw_sarp2_core_schedule"],
                    'main_table.profile_id = ascs.profile_id',
                    'ascs.profile_id'
                )->joinLeft(
                    ["ascsi" => "aw_sarp2_core_schedule_item"],
                    'ascs.schedule_id = ascsi.schedule_id',
                    'ascsi.scheduled_at as scheduled_at'
                );
            }
            return $this->subscriptionProfileCollection;
        }
        return $result;
    }
}
