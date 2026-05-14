<?php
namespace Abbott\Impersonation\Block\Adminhtml\Impersonation\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('impersonation_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Impersonation Information'));
    }
}
