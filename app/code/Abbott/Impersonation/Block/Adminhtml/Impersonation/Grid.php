<?php

namespace Abbott\Impersonation\Block\Adminhtml\Impersonation;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Abbott\Impersonation\Model\impersonationFactory
     */
    protected $_impersonationFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Abbott\Impersonation\Model\impersonationFactory $impersonationFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Abbott\Impersonation\Model\ImpersonationFactory $impersonationFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_impersonationFactory = $impersonationFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('login_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_impersonationFactory->create()->getCollection();
        $collection->getSelect()
            ->joinLeft(
                ['c' => 'customer_entity'],
                'c.entity_id = main_table.customer_id',
                ['email']
            )->joinLeft(
                ['a' => 'admin_user'],
                'a.user_id = main_table.admin_id',
                ['username']
            );
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'login_id',
            [
                'header' => __('ID'),
                'index' => 'login_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'customer_id',
            [
                'header' => __('Customer ID'),
                'index' => 'customer_id',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Customer Email'),
                'index' => 'email',
            ]
        );

        $this->addColumn(
            'admin_id',
            [
                'header' => __('Admin ID'),
                'index' => 'admin_id',
            ]
        );

        $this->addColumn(
            'username',
            [
                'header' => __('Admin Name'),
                'index' => 'username',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Logged In'),
                'index' => 'created_at',
                'type'      => 'datetime',
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('impersonation/*/index', ['_current' => true]);
    }

    /**
     * @param \Abbott\Impersonation\Model\impersonation|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '#';
    }
    
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Abbott_Impersonation::login_log');
    }
}
