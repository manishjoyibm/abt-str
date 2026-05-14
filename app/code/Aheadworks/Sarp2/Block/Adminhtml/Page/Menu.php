<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Adminhtml\Page;

/**
 * Page Menu
 *
 * @method Menu setTitle(string $title)
 * @method string getTitle()
 *
 * @package Aheadworks\Sarp2\Block\Adminhtml\Page
 * @codeCoverageIgnore
 */
class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @inheritdoc
     */
    protected $_template = 'Aheadworks_Sarp2::page/menu.phtml';
}
