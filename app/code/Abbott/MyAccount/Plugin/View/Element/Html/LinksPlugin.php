<?php

namespace Abbott\MyAccount\Plugin\View\Element\Html;

use Closure;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Html\Links;
use Abbott\MyAccount\Helper\LinkData;
use Abbott\MyAccount\Model\Config\Source\Action;

class LinksPlugin
{

    /** @var Data */
    protected $_helperData;

    /**
     * LinksPlugin constructor.
     * @param Data $helperData
     */
    public function __construct(
        LinkData $helperData
    ) {
        $this->_helperData = $helperData;   
    }

    public function aroundRenderLink(Links $subject, Closure $proceed, AbstractBlock $link)
    {
        $output = $proceed($link);
        if ($this->_helperData->isEnabled()) {
            if (Action::EXCLUDE_SELECTED == $this->_helperData->getAction() && in_array($link->getNameInLayout(), $this->_helperData->getSectionList())) {
                   return '';
                }
            }      

        return $output;
    }
    
}
