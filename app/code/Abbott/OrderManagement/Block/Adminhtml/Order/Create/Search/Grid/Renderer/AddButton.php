<?php


namespace Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

use Magento\Framework\View\Helper\SecureHtmlRenderer;

class AddButton extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    protected SecureHtmlRenderer $secureRenderer;

    public function __construct(
        SecureHtmlRenderer $secureRenderer
    ) {
        $this->secureRenderer = $secureRenderer;
    }

    /**
     * Render product add field
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<div class="action-default scalable action-add action-secondary" 
                id="search_grid_add_btn_'.(int) $row->getId().'" >Add</div>';
        $html .= $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            'order.addProductButton(jQuery(this), '.$row->getId().')',
            'div#search_grid_add_btn_' . $row->getId()
        );
        return $html;
    }
}
