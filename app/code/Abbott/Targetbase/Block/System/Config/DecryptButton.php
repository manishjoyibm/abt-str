<?php
namespace Abbott\Targetbase\Block\System\Config;

class DecryptButton extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Abbott_Targetbase::system/config/DecryptButton.phtml';

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
        return $this->getUrl('targetbase/targetbase/filedecrypt');
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'tb_derypt_file',
                'label' => __('Decrypt'),
            ]
        );

        return $button->toHtml();
    }
}
