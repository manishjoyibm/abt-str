<?php
namespace Abbott\AbbottReport\Block\Adminhtml\Report\Customer;

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
        $this->_controller = 'adminhtml_report_customer_view';
        $this->_headerText = __('Customer Subscription Report');
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

        return $this->getUrl('*/*/customersubscription', ['_current' => true]);
    }
}
