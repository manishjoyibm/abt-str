<?php
namespace Abbott\AbbottReport\Model\ResourceModel\Report;

/**
 * ProductSubscription report resource model.
 */
class ProductSubscription extends \Magento\Sales\Model\ResourceModel\Report\AbstractReport
{
    const PERIOD = 'period';
    const STOREID = 'store_id';
    const PRODUCTID = 'product_id';

    /**
     * Model initialization.
     */
    protected function _construct()
    {
        $this->_init('aggregated_product_subscription', 'id');
    }

    /**
     * Aggregate subscription product by subscription created at.
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
        $this->_aggregateBySubscriptionProduct($from, $to);
        $this->_setFlagData(\Abbott\AbbottReport\Model\Flag::REPORT_PRODUCT_SUBSCRIPTION_FLAG_CODE);

        return $this;
    }

    /**
     * Aggregate subscription product by create_at as period.
     *
     * @param string|null $from
     * @param string|null $to
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected function _aggregateBySubscriptionProduct($from, $to)
    {
        $table = $this->getTable('aggregated_product_subscription');
        $sourceTable = $this->getTable('aw_sarp2_profile_item');
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
                $this->getStoreTZOffsetQuery($sourceTable, 'aw_sarp2_profile_item.created_at', $to, $from)
            );

            $columns = [
                self::PERIOD => $periodExpr,
                self::STOREID => self::STOREID,
                self::PRODUCTID => self::PRODUCTID,
                'product_name' => 'name',
                'subscriber_count' => new \Zend_Db_Expr('COUNT(aw_sarp2_profile_item.profile_id)'),
                'active_subscriber' => new \Zend_Db_Expr(
                    'SUM(aw_sarp2_profile.status = "active")'
                ),
                'suspended_subscriber' => new \Zend_Db_Expr(
                    'SUM(aw_sarp2_profile.status = "suspended")'
                ),
                'cancel_subscriber' => new \Zend_Db_Expr(
                    'SUM(aw_sarp2_profile.status = "cancelled")'
                ),
            ];

            $select = $connection->select();

            $select->from(
                $sourceTable,
                $columns
            )
                    ->join(
                        ['aw_sarp2_profile' => $this->getTable('aw_sarp2_profile')],
                        "aw_sarp2_profile.profile_id = aw_sarp2_profile_item.profile_id",
                        []
                    );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, self::PERIOD));
            }
            $select->group([$periodExpr, self::STOREID, self::PRODUCTID]);
            $select->having('subscriber_count > 0');
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
            $select->reset();

            $columns = [
                self::PERIOD => self::PERIOD,
                self::STOREID => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                self::PRODUCTID => self::PRODUCTID,
                'product_name' => new \Zend_Db_Expr('MIN(product_name)'),
                'subscriber_count' => new \Zend_Db_Expr('COUNT(subscriber_count)'),
                'active_subscriber' => new \Zend_Db_Expr('SUM(active_subscriber)'),
                'suspended_subscriber' => new \Zend_Db_Expr('SUM(suspended_subscriber)'),
                'cancel_subscriber' => new \Zend_Db_Expr('SUM(cancel_subscriber)'),
                    //   'no_of_occurrence' => new \Zend_Db_Expr('COUNT(no_of_occurrence)'),
            ];
            $select->from($table, $columns)->where('store_id != ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, self::PERIOD));
            }
            $select->group([self::PERIOD, self::PRODUCTID]);
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
