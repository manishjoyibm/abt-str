<?php
namespace Abbott\AbbottReport\Block\Adminhtml\Report\Product;

class View extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'report/grid/container.phtml';

    /**
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Abbott_AbbottReport';
        $this->_controller = 'adminhtml_report_product_view';
        $this->_headerText = __('Product Subscription Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            [
                    'label' => __('Show Report'),
                    'onclick' => 'filterFormSubmit()',
                    'class' => 'primary'
                ]
        );
    }

    /**
     * Get filter url.
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);

        return $this->getUrl('*/*/productsubscription', ['_current' => true]);
    }
}
