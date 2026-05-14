<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Aheadworks\Sarp2\Model\ResourceModel\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'profile_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Profile::class, ProfileResource::class);
        $this->_map['fields']['store_id'] = 'main_table.store_id';
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (in_array($field, [ProfileItemInterface::PRODUCT_ID])) {
            $this->addFilter($field, $condition, 'public');
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinLinkageTable(
            'aw_sarp2_profile_item',
            ProfileInterface::PROFILE_ID,
            ProfileItemInterface::PROFILE_ID,
            ProfileItemInterface::PRODUCT_ID,
            ProfileItemInterface::PRODUCT_ID
        );
        parent::_renderFiltersBefore();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachItems();
        return parent::_afterLoad();
    }

    /**
     * Attach profile items data to collection
     *
     * @return void
     */
    private function attachItems()
    {
        $profileIds = $this->getColumnValues('profile_id');
        if (count($profileIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['profile_item_table' => $this->getTable('aw_sarp2_profile_item')])
                ->where('profile_item_table.profile_id IN (?)', $profileIds)
                ->where('profile_item_table.parent_item_id IS NULL');
            $itemsData = $connection->fetchAll($select);

            /** @var \Magento\Framework\DataObject $profile */
            foreach ($this as $profile) {
                $profileId = $profile->getData('profile_id');
                $items = [];
                foreach ($itemsData as $data) {
                    if ($data['profile_id'] == $profileId) {
                        $items[] = $data;
                    }
                }
                $profile->setData('items', $items);
            }
        }
    }
}
