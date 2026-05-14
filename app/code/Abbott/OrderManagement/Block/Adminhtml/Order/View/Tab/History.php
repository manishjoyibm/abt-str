<?php

namespace Abbott\OrderManagement\Block\Adminhtml\Order\View\Tab;

class History extends \Magento\Sales\Block\Adminhtml\Order\View\Tab\History
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/history.phtml';

    /**
     * Status history item admin username getter
     *
     * @param array $item
     * @return string
     */
    public function getItemAdminUsername(array $item)
    {
        return array_key_exists('admin_username', $item) ? $this->escapeHtml($item['admin_username']) : '';
    }
}
