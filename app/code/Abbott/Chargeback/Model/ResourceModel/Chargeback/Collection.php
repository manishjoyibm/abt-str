<?php

namespace Abbott\Chargeback\Model\ResourceModel\Chargeback;

class Collection extends \Abbott\Chargeback\Model\ResourceModel\AbstractCollection
{
    /**
     * @var bool
     */
    public $_previewFlag;
    /**
     * @var string
     */
    protected $_idFieldName = 'chargeback_id';
    
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'abbott_chargebacklog_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'chargebacklog_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\Chargeback\Model\Chargeback::class,
            \Abbott\Chargeback\Model\ResourceModel\Chargeback::class
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
