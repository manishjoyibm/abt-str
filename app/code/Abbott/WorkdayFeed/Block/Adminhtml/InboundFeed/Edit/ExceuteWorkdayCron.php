<?php

namespace Abbott\WorkdayFeed\Block\Adminhtml\InboundFeed\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ExceuteWorkdayCron extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('SFTP Sync'),
            'on_click' => 'deleteConfirm(\'' . __(
                'Are you sure you want run Workday Feed from SFTP?'
            ) . '\', \'' . $this->getSFTPUrl() . '\')',
            'class' => 'primary',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getSFTPUrl(): string
    {
        return $this->context->getUrlBuilder()->getUrl('abbott/inboundfeed/WorkdaySFTPReader', ['_current' => true]);
    }
}
