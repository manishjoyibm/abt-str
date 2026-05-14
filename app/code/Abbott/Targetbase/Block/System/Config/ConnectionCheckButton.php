<?php
namespace Abbott\Targetbase\Block\System\Config;

class ConnectionCheckButton extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Abbott_Targetbase::system/config/ConnectionCheckButton.phtml';

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
    public function getAjaxUrl()
    {
        return $this->getUrl('targetbase/targetbase/Connectioncheck');
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'tb_connection_check',
                'label' => __('Connection Check'),
            ]
        );

        return $button->toHtml();
    }
}
