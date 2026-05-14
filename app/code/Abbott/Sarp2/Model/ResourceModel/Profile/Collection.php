<?php
namespace Abbott\Sarp2\Model\ResourceModel\Profile;

/**
 * Class Collection
 *
 * Overwritten to add product name filter
 */
class Collection extends \Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection
{
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
                        $items = $data['name'];
                    }
                }
                $profile->setData('product_name', $items);
            }
        }
    }
}
