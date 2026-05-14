<?php
declare(strict_types=1);

namespace Abbott\Csp\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class Type extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Get Source Options
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
         return [
             [
                'value' => 'host',
                'label' => 'url'
             ],
             [
                 'value' => 'hash',
                 'label' => 'hash'
             ]
         ];
    }
}
