<?php
namespace Abbott\Targetbase\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class SyncFileField extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        return $element->getElementHtml();
    }
}
