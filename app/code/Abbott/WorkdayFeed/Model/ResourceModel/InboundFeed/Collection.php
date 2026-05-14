<?php

namespace Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed;

use Abbott\WorkdayFeed\Model\InboundFeed;
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
    protected $_idFieldName = 'feed_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected bool $_previewFlag;

    /**
     * @var string
     */
    protected $_eventPrefix = 'abbott_workdayfeed_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'workdayfeed_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            InboundFeed::class,
            \Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed::class
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
