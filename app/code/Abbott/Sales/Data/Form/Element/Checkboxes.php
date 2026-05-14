<?php

namespace Abbott\Sales\Data\Form\Element;

use Magento\Framework\Escaper;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;

/**
 * Form select element
 *
 */
class Checkboxes extends \Magento\Framework\Data\Form\Element\Checkboxes
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('checkbox');
        $this->setExtType('checkboxes');
    }

    /**
     * Render a checkbox.
     *
     * @param array $option
     * @return string
     */
    protected function _optionToHtml($option)
    {
        $id = $this->getHtmlId() . '_' . $this->_escape($option['value']);

        $html = '<div class="field choice admin__field admin__field-option"><input id="' . $id . '"';
        foreach ($this->getHtmlAttributes() as $attribute) {
            if ($value = $this->getDataUsingMethod($attribute, $option['value'])) {
                if ($this->getRequired()) {
                    $html .= ' ' . $attribute . '="' . $value
                            . '" class="admin__control-checkbox required-entry _required"';
                } else {
                    $html .= ' ' . $attribute . '="' . $value . '" class="admin__control-checkbox"';
                }
            }
        }
        $html .= ' value="' .
            $option['value'] .
            '" />' .
            ' <label for="' .
            $id .
            '" class="admin__field-label"><span>' .
            $option['label'] .
            '</span></label></div>' .
            "\n";
        return $html;
    }
}
