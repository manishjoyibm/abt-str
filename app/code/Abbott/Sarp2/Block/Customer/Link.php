<?php

namespace Abbott\Sarp2\Block\Customer;

use Magento\Customer\Block\Account\SortLinkInterface;

class Link extends \Magento\Framework\View\Element\Html\Link\Current implements SortLinkInterface
{

    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
