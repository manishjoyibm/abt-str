<?php

namespace Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search;

use Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search\Grid\Renderer\AddButton;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\DataProvider\ProductCollection;
use Magento\Framework\App\ObjectManager;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const ENTITY_ID = 'entity_id';
    const IN_PRODUCTS = 'in_products';
    const HEADER = 'header';
    const SORTABLE = 'sortable';
    const COLUMN_CSS_CLASS = 'column_css_class';
    const INDEX = 'index';
    const RENDERER = 'renderer';
    const PRICE = 'price';

    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $salesConfig;

    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductCollection $productCollectionProvider
     */
    private $productCollectionProvider;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param array $data
     * @param ProductCollection|null $productCollectionProvider
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\Config $salesConfig,
        array $data = [],
        ProductCollection $productCollectionProvider = null
    ) {
        $this->productFactory = $productFactory;
        $this->catalogConfig = $catalogConfig;
        $this->sessionQuote = $sessionQuote;
        $this->salesConfig = $salesConfig;
        $this->productCollectionProvider = $productCollectionProvider
            ?: ObjectManager::getInstance()->get(ProductCollection::class);
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_search_grid');
        $this->setRowClickCallback('order.productGridRowClick.bind(order)');
        $this->setCheckboxCheckCallback('order.productGridCheckboxCheck.bind(order)');
        $this->setRowInitCallback('order.productGridRowInit.bind(order)');
        $this->setDefaultSort(self::ENTITY_ID);
        $this->setFilterKeyPressCallback('order.productGridFilterKeyPress');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Retrieve quote store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->sessionQuote->getStore();
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->sessionQuote->getQuote();
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
      // Set custom filter for in product flag
        if ($column->getId() == self::IN_PRODUCTS) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter(self::ENTITY_ID, ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter(self::ENTITY_ID, ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {

        $attributes = $this->catalogConfig->getProductAttributes();
        $store = $this->getStore();

        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionProvider->getCollectionForStore($store);
        $collection->addAttributeToSelect(
            $attributes
        );
        $collection->addFieldToFilter('type_id', ['eq'=>'simple']);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $status = ObjectManager::getInstance()->get(
            '\Magento\Catalog\Model\Product\Attribute\Source\Status'
        )->getOptionArray();

        $this->addColumn(
            self::ENTITY_ID,
            [
                self::HEADER => __('ID'),
                self::SORTABLE => true,
                'header_css_class' => 'col-id',
                self::COLUMN_CSS_CLASS => 'col-id',
                self::INDEX => self::ENTITY_ID
            ]
        );
        $this->addColumn(
            'name',
            [
                self::HEADER => __('Product'),
                self::RENDERER => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product::class,
                self::INDEX => 'name'
            ]
        );
        $this->addColumn('sku', [self::HEADER => __('SKU'), self::INDEX => 'sku']);
        $this->addColumn(
            'cases',
            [
            self::HEADER => __('Sizes'),
            self::RENDERER => \Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Sizes::class,
            self::INDEX => 'cases'
            ]
        );
        $this->addColumn(
            'product_flavor',
            [
            self::HEADER => __('Flavor'),
            self::RENDERER => \Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Flavors::class,
            self::INDEX => 'product_flavor'
            ]
        );
        $this->addColumn(
            'product_form',
            [
            self::HEADER => __('Form'),
            self::RENDERER => \Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Forms::class,
            self::INDEX => 'product_form'
            ]
        );
        $this->addColumn(
            self::PRICE,
            [
              self::HEADER => __('Price'),
              self::COLUMN_CSS_CLASS => self::PRICE,
              'type' => 'currency',
              'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
              'rate' => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
              self::INDEX => self::PRICE,
              self::RENDERER => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price::class
            ]
        );
        $this->addColumn(
            self::IN_PRODUCTS,
            [
              self::HEADER => __('Select'),
              'type' => 'checkbox',
              'name' => self::IN_PRODUCTS,
              'values' => $this->_getSelectedProducts(),
              self::INDEX => self::ENTITY_ID,
              self::SORTABLE => false,
              'header_css_class' => 'col-select',
              self::COLUMN_CSS_CLASS => 'col-select'
            ]
        );

        $this->addColumn(
            'qty',
            [
                'filter' => false,
                self::SORTABLE => false,
                self::HEADER => __('Quantity'),
                self::RENDERER => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty::class,
                'name' => 'qty',
                'inline_css' => 'qty',
                'type' => 'input',
                'validate_class' => 'validate-number',
                self::INDEX => 'qty'
            ]
        );

        $this->addColumn(
            'add',
            [
                'header' => __('Add'),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
                self::RENDERER => AddButton::class,
            ]
        );

        $this->addColumnAfter(
            'status',
            [
            self::HEADER    => 'Status',
            'type'    => 'options',
            self::INDEX => 'status',
            'options' => $status
            ],
            'qty'
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'sales/*/loadBlock',
            ['block' => 'search_grid', '_current' => true, 'collapse' => null]
        );
    }

    /**
     * Get selected products
     *
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        return $this->getRequest()->getPost('products', []);
    }

    /**
     * Add custom options to product collection
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addOptionsToResult();
        return parent::_afterLoadCollection();
    }
}
