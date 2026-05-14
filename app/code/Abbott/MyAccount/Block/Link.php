<?php
namespace Abbott\MyAccount\Block;

/**
 * RMA Return Block
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current implements
    \Magento\Customer\Block\Account\SortLinkInterface
{
    /**
     * GetSortOrder function
     *
     * @return array|int|mixed|null
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
