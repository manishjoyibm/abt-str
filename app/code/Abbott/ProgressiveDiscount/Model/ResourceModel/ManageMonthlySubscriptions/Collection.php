<?php

namespace Abbott\ProgressiveDiscount\Model\ResourceModel\ManageMonthlySubscriptions;

use Abbott\ProgressiveDiscount\Model\ResourceModel\AbstractCollection;

/**
 * InboundFeed collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'row_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'monthly_subscriptions_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'monthly_subscriptions_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\ProgressiveDiscount\Model\ManageMonthlySubscriptions::class,
            \Abbott\ProgressiveDiscount\Model\ResourceModel\ManageMonthlySubscriptions::class
        );
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        return $this;
    }
}
