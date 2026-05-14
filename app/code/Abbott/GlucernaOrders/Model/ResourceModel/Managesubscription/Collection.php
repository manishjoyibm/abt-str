<?php

namespace Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription;

use Abbott\GlucernaOrders\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var bool
     */
    public $_previewFlag;
    /**
     * @var string
     */
    protected $_idFieldName = 'managesubscription_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'abbott_glucernaorders_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'glucernaorders_collection';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Abbott\GlucernaOrders\Model\Managesubscription::class,
            \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription::class
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
