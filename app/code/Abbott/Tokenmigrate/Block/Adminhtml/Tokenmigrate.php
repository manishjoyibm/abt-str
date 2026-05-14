<?php

namespace Abbott\Tokenmigrate\Block\Adminhtml;

use Magento\Catalog\Block\Adminhtml\Product;

class Tokenmigrate extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'tokenmigrate/tokenmigrate.phtml';

    /**
     * Prepare button and grid
     *
     * @return Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Run Script'),
            'class' => 'add',
            'button_class' => '',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
        ];
        $this->buttonList->add('add_new', $addButtonProps);
        return parent::_prepareLayout();
    }

    /**
     * GetCreated Url
     *
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'tokenmigrate/*/new'
        );
    }
}
