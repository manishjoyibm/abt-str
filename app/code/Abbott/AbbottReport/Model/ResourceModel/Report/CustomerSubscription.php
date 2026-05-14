<?php
namespace Abbott\AbbottReport\Model\ResourceModel\Report;

/**
 * CustomerSubscription report resource model.
 */
class CustomerSubscription extends \Magento\Sales\Model\ResourceModel\Report\AbstractReport
{
    const PERIOD = 'period';
    const STOREID = 'store_id';
    const CUSTOMERID = 'customer_id';
    const CUSTOMEREMAIL = 'customer_email';
    /**
     * Model initialization.
     */
    protected function _construct()
    {
        $this->_init('aggregated_customer_subscription', 'id');
    }

    /**
     * Aggregate subscription customer by subscription created at.
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     *
     * @return $this
     *
     * @throws \Exception
     * @SuppressWarnings(PHPMagedelight.ExcessiveMethodLength)
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_aggregateBySubscriptionCustomer($from, $to);
        $this->_setFlagData(\Abbott\AbbottReport\Model\Flag::REPORT_CUSTOMER_SUBSCRIPTION_FLAG_CODE);

        return $this;
    }

    /**
     * Aggregate subscription customer by create_at as period.
     *
     * @param string|null $from
     * @param string|null $to
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected function _aggregateBySubscriptionCustomer($from, $to)
    {
        $table = $this->getTable('aggregated_customer_subscription');
        $sourceTable = $this->getTable('aw_sarp2_profile');
        $connection = $this->getConnection();
        $connection->truncateTable($table);
        $connection->beginTransaction();
        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect($sourceTable, 'created_at', 'updated_at', $to, $from);
            } else {
                $subSelect = null;
            }
            // convert dates to current admin timezone
            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery($sourceTable, 'created_at', $from, $to)
            );

            $columns = [
                self::PERIOD => $periodExpr,
                self::STOREID => self::STOREID,
                self::CUSTOMERID => self::CUSTOMERID,
                'customer_name' => 'customer_fullname',
                self::CUSTOMEREMAIL => self::CUSTOMEREMAIL,
                'subscriber_count' => new \Zend_Db_Expr('COUNT(profile_id)'),
                'active_subscriber' => new \Zend_Db_Expr(
                    'SUM(status = "active")'
                ),
                'suspended_subscriber' => new \Zend_Db_Expr(
                    'SUM(status = "suspended")'
                ),
                'cancel_subscriber' => new \Zend_Db_Expr(
                    'SUM(status = "cancelled")'
                ),
            ];
            $select = $connection->select();
            $select->from(
                $sourceTable,
                $columns
            );
            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, self::PERIOD));
            }
            $select->group([$periodExpr, self::STOREID, self::CUSTOMERID]);
            $select->having('subscriber_count > 0');
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
            $select->reset();

            $columns = [
                self::PERIOD => self::PERIOD,
                self::STOREID => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                self::CUSTOMERID => self::CUSTOMERID,
                'customer_name' => new \Zend_Db_Expr('MIN(customer_name)'),
                self::CUSTOMEREMAIL => new \Zend_Db_Expr('MIN(customer_email)'),
                'subscriber_count' => new \Zend_Db_Expr('COUNT(subscriber_count)'),
                'active_subscriber' => new \Zend_Db_Expr('SUM(active_subscriber)'),
                'suspended_subscriber' => new \Zend_Db_Expr('SUM(suspended_subscriber)'),
                'cancel_subscriber' => new \Zend_Db_Expr('SUM(cancel_subscriber)'),
            ];
            $select->from($table, $columns)->where('store_id != ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, self::PERIOD));
            }
            $select->group([self::PERIOD, self::CUSTOMERID]);
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();

        return $this;
    }
}
