<?php


namespace Abbott\Subscriptionhistory\Block\Adminhtml\Subscriptionhistory\Grid;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class DateRenderer extends AbstractRenderer
{

    /**
     * @param Object $row
     * @return string
     */
    public function render(DataObject  $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->date(new \DateTime($row->getCreatedAt()))->format('M d, Y H:i:s');
    }
}
