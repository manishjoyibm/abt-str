<?php

namespace Abbott\Chargeback\Block\Adminhtml\Chargeback\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SftpSync extends GenericButton implements ButtonProviderInterface
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
                'Are you sure you want run Chargeback Feed from SFTP?'
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
        return $this->context->getUrlBuilder()->getUrl(
            'abbott_chargeback/chargeback/ChargebackSFTPReader',
            ['_current' => true]
        );
    }
}
