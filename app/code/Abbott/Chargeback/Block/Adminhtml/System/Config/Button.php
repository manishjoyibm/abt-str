<?php

namespace Abbott\Chargeback\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Abbott_Chargeback::system/config/button.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    
    /**
     * @return string
     */
    public function getCustomUrl()
    {
        return $this->getUrl('abbott_chargeback/chargeback/validate', ['_current' => true]);
    }
    
    /**
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'sent',
                'label' => __('Validate Details'),
            ]
        );
        return $button->toHtml();
    }
}
