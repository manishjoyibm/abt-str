<?php

namespace Abbott\WorkdayFeed\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * InboundFeed content block
 *
 * @api
 */
class InboundFeed extends Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_controller = 'adminhtml_inboundfeed';
        $this->_blockGroup = 'Abbott_WorkdayFeed';
        $this->_headerText = __('Manage InboundFeed');

        parent::_construct();

        if ($this->_isAllowedAction('Abbott_WorkdayFeed::save')) {
            $this->buttonList->update('add', 'label', __('Add New Workday Feed'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction(string $resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
