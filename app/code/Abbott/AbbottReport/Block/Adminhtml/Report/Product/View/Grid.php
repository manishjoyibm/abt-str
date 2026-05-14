<?php
namespace Abbott\AbbottReport\Block\Adminhtml\Report\Product\View;

use Abbott\AbbottReport\Model\ResourceModel\Report\Subscription\Product\Collection;

class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    const PERIOD = 'period';
    const HEADER = 'header';
    const INDEX = 'index';
    const SORTABLE = 'sortable';
    const HEADERCSSCLASS = 'header_css_class';
    const COLUMNCSSCLASS = 'column_css_class';
    const COLNAME = 'col-name';
    const TOTAL = 'total';
    const NUMBER = 'number';
    const COLQTY = 'col-qty';
    protected $_columnGroupBy = self::PERIOD;

    /**
     * Grid resource collection name.
     *
     * @var string
     */
    protected $_resourceCollectionName = Collection::class;

    /**
     * Init grid parameters.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }


    /**
     * Custom columns preparation.
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            self::PERIOD,
            [
            self::HEADER => __('Interval'),
            self::INDEX => self::PERIOD,
            self::SORTABLE => false,
            'period_type' => $this->getPeriodType(),
            'renderer' => \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date::class,
            'totals_label' => __(self::TOTAL),
            'subtotals_label' => __('Subtotal'),
            'html_decorators' => ['nobr'],
            self::HEADERCSSCLASS => 'col-period',
            self::COLUMNCSSCLASS => 'col-period',
                ]
        );

        $this->addColumn(
            'product_name',
            [
                self::HEADER => __('Product Name'),
                self::INDEX => 'product_name',
                'type' => 'string',
                self::SORTABLE => false,
                self::HEADERCSSCLASS => self::COLNAME,
                self::COLUMNCSSCLASS => self::COLNAME,
            ]
        );

        $this->addColumn(
            'subscriber_count',
            [
            self::HEADER => __('Subscriber Count'),
            self::INDEX => 'subscriber_count',
            self::TOTAL => 'sum',
            'type' => self::NUMBER,
            self::SORTABLE => false,
            self::HEADERCSSCLASS => self::COLQTY,
            self::COLUMNCSSCLASS => self::COLQTY,
                ]
        );

        $this->addColumn(
            'active_subscriber',
            [
            self::HEADER => __('Active Subscriber'),
            self::INDEX => 'active_subscriber',
            self::TOTAL => 'sum',
            'type' => self::NUMBER,
            self::SORTABLE => false,
            self::HEADERCSSCLASS => self::COLQTY,
            self::COLUMNCSSCLASS => self::COLQTY,
                ]
        );

        $this->addColumn(
            'suspended_subscriber',
            [
            self::HEADER => __('Suspended Subscriber'),
            self::INDEX => 'suspended_subscriber',
            self::TOTAL => 'sum',
            'type' => self::NUMBER,
            self::SORTABLE => false,
            self::HEADERCSSCLASS => self::COLQTY,
            self::COLUMNCSSCLASS => self::COLQTY,
                ]
        );
        $this->addColumn(
            'cancel_subscriber',
            [
            self::HEADER => __('Cancel Subscriber'),
            self::INDEX => 'cancel_subscriber',
            self::TOTAL => 'sum',
            'type' => self::NUMBER,
            self::SORTABLE => false,
            self::HEADERCSSCLASS => self::COLQTY,
            self::COLUMNCSSCLASS => self::COLQTY,
                ]
        );

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        $this->addExportType('abbottreport/report/exportSubscriptionProductCsv', __('CSV'));
        $this->addExportType('abbottreport/report/exportSubscriptionProductExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
