<?php


namespace Abbott\Subscriptionhistory\Block\Adminhtml\SubscriptionHistory\Grid;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Renderer extends AbstractRenderer
{
    /**
     * @param Object $row
     * @return string
     */
    public function render(DataObject  $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('Abbott\Subscriptionhistory\Helper\HistoryMessages')->getDataChanged($row);
    }
}
