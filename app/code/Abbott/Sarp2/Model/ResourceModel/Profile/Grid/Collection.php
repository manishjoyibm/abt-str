<?php
namespace Abbott\Sarp2\Model\ResourceModel\Profile\Grid;

/**
 * Class Collection
 *
 * Overwritten to add product name filter
 */
class Collection extends \Aheadworks\Sarp2\Model\ResourceModel\Profile\Grid\Collection
{
    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
        ->joinLeft(
                ['aw_sarp2_plan_table' => $this->getTable('aw_sarp2_plan')],
                'main_table.plan_id = aw_sarp2_plan_table.plan_id',
                ['aw_sarp2_plan_table.is_progressive as is_progressive']
            )
        ->joinLeft(
            ['aw_sarp2_profile_item' => $this->getTable('aw_sarp2_profile_item')],
            "main_table.profile_id = aw_sarp2_profile_item.profile_id",
            ['GROUP_CONCAT(aw_sarp2_profile_item.name) as product_name']
        )
        ->group('main_table.profile_id');
        $this->addFilterToMap('is_progressive', 'aw_sarp2_plan_table.is_progressive');
        $this->getSelect()->columns('aw_sarp2_plan_table.is_progressive as is_progressive');
        
        $this->addFilterToMap('product_name', 'aw_sarp2_profile_item.name');
        $this->getSelect()->columns('GROUP_CONCAT(aw_sarp2_profile_item.name) as product_name');
        $this->addFilterToMap('profile_table_id', 'main_table.profile_id');
        $this->getSelect()->columns('main_table.profile_id as profile_table_id');
        
        return $this;
    }
}
