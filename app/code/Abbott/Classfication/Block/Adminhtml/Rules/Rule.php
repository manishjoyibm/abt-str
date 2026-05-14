<?php

namespace Abbott\Classfication\Block\Adminhtml\Rules;

class Rule extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'rules_rule';
        $this->_headerText = __('Order Classfication');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}
