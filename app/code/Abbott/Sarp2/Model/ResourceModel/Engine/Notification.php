<?php
namespace Abbott\Sarp2\Model\ResourceModel\Engine;

use Aheadworks\Sarp2\Model\ResourceModel\Engine\Notification as OriginalNotification;

class Notification extends OriginalNotification
{
    /**
     * Overriding a method or adding custom function
     */
    public function getLatestUpcomingBilling($profileId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['notification_data', 'profile_id'])
            ->where('type = ?', 'upcoming_billing')
            ->where('store_id = ?', (int)$storeId)
            ->where('profile_id = ?', (int)$profileId)
            ->order('notification_id DESC')
            ->limit(1);
        
        return $connection->fetchRow($select);
    }
}