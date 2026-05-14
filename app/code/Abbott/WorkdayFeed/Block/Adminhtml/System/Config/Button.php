<?php

namespace Abbott\WorkdayFeed\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class Button extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Abbott_WorkdayFeed::system/config/button.phtml';

    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * Get Custom Url
     *
     * @return string
     */
    public function getCustomUrl(): string
    {
        return $this->getUrl('abbott/workday/validate', ['_current' => true]);
    }

    /**
     * Get Button Html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml(): string
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
