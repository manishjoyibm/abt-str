<?php

namespace Abbott\SmartCart\Block\Adminhtml\SmartCart\Tab;

use Abbott\SmartCart\Model\SmartCartFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Productgrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Manager
     */
    public $moduleManager;
    public $visibility;
    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var SmartCartFactory
     */
    protected $smartCartFactory;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Registry $coreRegistry
     * @param Manager $moduleManager
     * @param StoreManagerInterface $storeManager
     * @param SmartCartFactory $smartCartFactory
     * @param Status $status
     * @param Visibility|null $visibility
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        CollectionFactory $productCollectionFactory,
        Registry $coreRegistry,
        Manager $moduleManager,
        StoreManagerInterface $storeManager,
        SmartCartFactory $smartCartFactory,
        Status $status,
        Visibility $visibility = null,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->moduleManager = $moduleManager;
        $this->_storeManager = $storeManager;
        $this->smartCartFactory = $smartCartFactory;
        $this->status = $status;
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct function
     *
     * @return void
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('smartcart_grid_products');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('id')) {
            $this->setDefaultFilter(['in_products' => 1]);
        } else {
            $this->setDefaultFilter(['in_products' => 0]);
        }
        $this->setSaveParametersInSession(true);
    }

    /**
     * GetStore
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * PrepareCollection
     *
     * @return $this|Productgrid
     * @throws NoSuchEntityException
     */
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToSelect(
            'status'
        )->addAttributeToSelect(
            'type_id'
        )->setStore(
            $store
        );
        $collection->addAttributeToSelect('price');
        $this->setCollection($collection);
        parent::_prepareCollection();
        foreach ($collection as &$prod) {
            if (isset($this->getSelectedProducts()[$prod->getId()])) {
                $prod->setQty($this->getSelectedProducts()[$prod->getId()]);
            }
        }
        return $this;
    }

    /**
     * AddColumnFilterToCollection
     *
     * @param Column $column
     * @return $this|Productgrid
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * PrepareColumns
     *
     * @return Productgrid
     * @throws NoSuchEntityException
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'type' => 'checkbox',
                'html_name' => 'products_id',
                'required' => true,
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id',
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'number',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray()
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $store = $this->_getStore();
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );
        $this->addColumn(
            'qty',
            [
                'header' => __('Qty'),
                'name' => 'qty',
                'width' => 60,
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'qty',
                'editable' => true,
                'edit_only' => true,
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * GetGridUrl
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/index/grids', ['_current' => true, 'id'=> $this->getRequest()->getParam('id')]);
    }

    /**
     * GetSelectedProducts
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        return array_keys($this->getSelectedProducts());
    }

    /**
     * GetSelectedProducts
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $smartCart = $this->coreRegistry->registry('smartcart');
        if (!$smartCart) {
            $cartId = $this->getParam('id');
            $smartCart = $this->smartCartFactory->create()->load($cartId);
        }
        return $smartCart->getProducts() ?? [];
    }
}
