<?php
namespace Abbott\AbbottReport\Model\ResourceModel\Report\Subscription\Product;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Report;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
{
    const PERIOD = 'period';
    /**
     * Period format.
     *
     * @var string
     */
    protected string $periodFormat;

    /**
     * Selected columns.
     *
     * @var array
     */
    protected array $selectedColumns = [];

    /**
     * @param EntityFactory $entityFactoryCollection
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategyDb
     * @param ManagerInterface $eventManager
     * @param Report $resourceReport
     * @param AdapterInterface|null $connection
     */
    public function __construct(
        EntityFactory $entityFactoryCollection,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategyDb,
        ManagerInterface $eventManager,
        Report $resourceReport,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $resourceReport->init('aggregated_product_subscription');
        parent::__construct(
            $entityFactoryCollection,
            $logger,
            $fetchStrategyDb,
            $eventManager,
            $resourceReport,
            $connection
        );
    }

    /**
     * Get selected columns.
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();
        if ('month' == $this->_period) {
            $this->periodFormat = $connection->getDateFormatSql(self::PERIOD, '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->periodFormat = $connection->getDateExtractSql(
                self::PERIOD,
                \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_YEAR
            );
        } else {
            $this->periodFormat = $connection->getDateFormatSql(self::PERIOD, '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->selectedColumns = [
                self::PERIOD => $this->periodFormat,
                'product_name' => 'MAX(product_name)',
                'subscriber_count' => 'SUM(subscriber_count)',
                'active_subscriber' => 'SUM(active_subscriber)',
                'suspended_subscriber' => 'SUM(suspended_subscriber)',
                'cancel_subscriber' => 'SUM(cancel_subscriber)'
            ];
        }

        if ($this->isTotals()) {
            $this->selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->selectedColumns = $this->getAggregatedColumns() + [self::PERIOD => $this->periodFormat];
        }

        return $this->selectedColumns;
    }

    /**
     * Apply custom columns before load.
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()->group([$this->periodFormat, 'product_name']);
        }
        if ($this->isSubTotals()) {
            $this->getSelect()->group([$this->periodFormat]);
        }

        return parent::_beforeLoad();
    }
}
