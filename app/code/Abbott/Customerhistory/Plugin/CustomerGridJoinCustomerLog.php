<?php
declare(strict_types=1);

namespace Abbott\Customerhistory\Plugin;

use Magento\Customer\Model\ResourceModel\Grid\Collection;

/**
 * Left-join core customer_log to add last login / last logout to grid collection.
 *
 * Uses Magento's table name resolver and keeps everything upgrade-safe.
 */
class CustomerGridJoinCustomerLog
{
    public function beforeLoad(Collection $subject)
    {
        if ($subject->isLoaded()) {
            return null;
        }

        $conn  = $subject->getConnection();
        $table = $subject->getTable('customer_log'); // core table

        // Join latest values per customer (customer_log keeps last_* fields)
        $subject->getSelect()->joinLeft(
            ['cl' => $table],
            'cl.customer_id = main_table.entity_id',
            [
                'last_login_at'  => 'cl.last_login_at',   // if present
                'last_logoff_at' => 'cl.last_logout_at'   // our column in grid
            ]
        );

        return null;
    }
}
