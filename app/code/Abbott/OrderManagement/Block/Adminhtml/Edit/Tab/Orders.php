<?php

namespace Abbott\OrderManagement\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

class Orders extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public $_collectionFactory;
    const CREATED_AT = 'created_at';
    const INCREMENT_ID = 'increment_id';
    const STATUS = 'status';
    const CUSTOMER_ID = 'customer_id';
    const GRAND_TOTAL = 'grand_total';
    const STORE_ID = 'store_id';
    const BILLING_NAME = 'billing_name';
    const SHIPPING_NAME = 'shipping_name';
    const INDEX = 'index';
    const HEADER = 'header';

    /**
     * Sales reorder
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $salesReorder = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Helper\Reorder $salesReorder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Magento\Sales\Helper\Reorder $salesReorder,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->salesReorder = $salesReorder;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_orders_grid');
        $this->setDefaultSort(self::CREATED_AT);
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->getReport('sales_order_grid_data_source')->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            self::INCREMENT_ID
        )->addFieldToSelect(
            self::STATUS
        )->addFieldToSelect(
            self::CUSTOMER_ID
        )->addFieldToSelect(
            self::CREATED_AT
        )->addFieldToSelect(
            self::GRAND_TOTAL
        )->addFieldToSelect(
            'order_currency_code'
        )->addFieldToSelect(
            self::STORE_ID
        )->addFieldToSelect(
            self::BILLING_NAME
        )->addFieldToSelect(
            self::SHIPPING_NAME
        )->addFieldToFilter(
            self::CUSTOMER_ID,
            $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            self::INCREMENT_ID,
            [self::HEADER => __('Order #'), 'width' => '100', self::INDEX => self::INCREMENT_ID]
        );

        $this->addColumn(
            self::CREATED_AT,
            [self::HEADER => __('Purchased'), self::INDEX => self::CREATED_AT, 'type' => 'datetime']
        );

        $this->addColumn(self::BILLING_NAME, [self::HEADER => __('Bill-to Name'), self::INDEX => self::BILLING_NAME]);

        $this->addColumn(self::SHIPPING_NAME, [self::HEADER => __('Ship-to Name'), self::INDEX => self::SHIPPING_NAME]);

        $this->addColumn(
            self::GRAND_TOTAL,
            [
                self::HEADER => __('Order Total'),
                self::INDEX => self::GRAND_TOTAL,
                'type' => 'currency',
                'currency' => 'order_currency_code',
                'rate'  => 1
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                self::STORE_ID,
                [
                    self::HEADER => __('Purchase Point'),
                    self::INDEX => self::STORE_ID,
                    'type' => 'store',
                    'store_view' => true
                ]
            );
        }

        $this->addColumn(self::STATUS, [self::HEADER => __('Status'), self::INDEX => self::STATUS]);

        if ($this->salesReorder->isAllow()) {
            $this->addColumn(
                'action',
                [
                    self::HEADER => 'Action',
                    'filter' => false,
                    'sortable' => false,
                    'width' => '100px',
                    'renderer' => \Magento\Sales\Block\Adminhtml\Reorder\Renderer\Action::class
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $row->getId(), self::CUSTOMER_ID =>  $this->getRequest()->getParam('id')]
        );
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('customer/*/orders', ['_current' => true]);
    }
}
