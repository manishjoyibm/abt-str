<?php

namespace Abbott\MetabolicOrdering\Plugin\Order\Create;

use Magento\Framework\Exception\LocalizedException;

class Search
{
    /**
     * Add custom class on buttons html
     *
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetButtonsHtml(\Magento\Sales\Block\Adminhtml\Order\Create\Search $subject, callable $proceed)
    {
        $addButtonData = [
            'label' => __('Add Selected Product(s) to Order'),
            'onclick' => 'order.productGridAddSelected()',
            'class' => 'action-add action-secondary add_button',
        ];
        return $subject->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            $addButtonData
        )->toHtml();
    }
}
