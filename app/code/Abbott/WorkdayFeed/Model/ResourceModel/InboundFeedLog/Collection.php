<?php

namespace Abbott\WorkdayFeed\Model\ResourceModel\InboundFeedLog;

use Abbott\WorkdayFeed\Model\InboundFeedLog;
use Abbott\WorkdayFeed\Model\ResourceModel\AbstractCollection;
use Magento\Store\Model\Store;

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
    protected bool $_previewFlag;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'abbott_workdayfeedlog_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'workdayfeedlog_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            InboundFeedLog::class,
            \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeedLog::class
        );
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag(bool $flag = false): static
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param array|int|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter(Store|array|int $store, bool $withAdmin = true): static
    {
        return $this;
    }
}
